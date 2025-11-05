<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryCheck;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display inventory items for kitchen staff with full CRUD capability
     */
    public function index(Request $request)
    {
        // Get all ingredients from the Ingredient model (which has the actual data)
        $allItems = Ingredient::orderBy('name')->get();

        // Calculate low stock items based on minimum_stock
        $lowStockItems = $allItems->filter(function ($item) {
            return $item->quantity <= ($item->minimum_stock ?? 0) && $item->quantity > 0;
        });

        // Get items by status for statistics
        $availableItems = $allItems->filter(function ($item) {
            return $item->quantity > ($item->minimum_stock ?? 0);
        });

        $outOfStockItems = $allItems->filter(function ($item) {
            return $item->quantity <= 0;
        });

        // Get all inventory checks for history section (paginated)
        $allChecks = InventoryCheck::with(['user', 'items'])
            ->where('user_id', Auth::user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('kitchen.inventory', compact(
            'allItems',
            'lowStockItems',
            'availableItems',
            'outOfStockItems',
            'allChecks'
        ));
    }

    /**
     * Store a new inventory item
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:inventory,name',
            'description' => 'nullable|string|max:500',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'reorder_point' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'unit_price' => 'nullable|numeric|min:0'
        ]);

        $inventory = Inventory::create([
            'name' => $request->name,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'reorder_point' => $request->reorder_point,
            'category' => $request->category,
            'location' => $request->location,
            'supplier' => $request->supplier,
            'unit_price' => $request->unit_price,
            'last_updated_by' => Auth::user()->user_id,
            'status' => $this->determineStatus($request->quantity, $request->reorder_point)
        ]);

        // Log inventory history
        InventoryHistory::create([
            'inventory_item_id' => $inventory->id,
            'user_id' => Auth::user()->user_id,
            'action_type' => 'created',
            'quantity_change' => $request->quantity,
            'previous_quantity' => 0,
            'new_quantity' => $request->quantity,
            'notes' => 'Initial inventory creation'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item created successfully',
            'item' => $inventory
        ]);
    }

    /**
     * Update an existing inventory item
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:inventory,name,' . $id,
            'description' => 'nullable|string|max:500',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'reorder_point' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'unit_price' => 'nullable|numeric|min:0'
        ]);

        $previousQuantity = $inventory->quantity;

        $inventory->update([
            'name' => $request->name,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'reorder_point' => $request->reorder_point,
            'category' => $request->category,
            'location' => $request->location,
            'supplier' => $request->supplier,
            'unit_price' => $request->unit_price,
            'last_updated_by' => Auth::user()->user_id,
            'status' => $this->determineStatus($request->quantity, $request->reorder_point)
        ]);

        // Log inventory history
        InventoryHistory::create([
            'inventory_item_id' => $inventory->id,
            'user_id' => Auth::user()->user_id,
            'action_type' => 'updated',
            'quantity_change' => $request->quantity - $previousQuantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $request->quantity,
            'notes' => 'Inventory item updated'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item updated successfully',
            'item' => $inventory
        ]);
    }

    /**
     * Delete an inventory item
     */
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);

        // Check if item is used in any purchase orders or recipes
        $hasDependencies = DB::table('purchase_order_items')->where('inventory_id', $id)->exists() ||
                          DB::table('recipe_ingredients')->where('inventory_id', $id)->exists();

        if ($hasDependencies) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this item as it is referenced in purchase orders or recipes'
            ], 400);
        }

        // Log deletion
        InventoryHistory::create([
            'inventory_item_id' => $inventory->id,
            'user_id' => Auth::user()->user_id,
            'action_type' => 'deleted',
            'quantity_change' => -$inventory->quantity,
            'previous_quantity' => $inventory->quantity,
            'new_quantity' => 0,
            'notes' => 'Inventory item deleted'
        ]);

        $inventory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory item deleted successfully'
        ]);
    }

    /**
     * Determine inventory status based on quantity and reorder point
     */
    private function determineStatus($quantity, $reorderPoint)
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        } elseif ($quantity <= $reorderPoint) {
            return 'low_stock';
        } else {
            return 'available';
        }
    }

    /**
     * Get inventory item details for reporting modal
     */
    public function getItem($id)
    {
        $item = Ingredient::findOrFail($id);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'current_quantity' => $item->quantity,
                'unit' => $item->unit,
                'reorder_point' => $item->minimum_stock ?? 0,
                'category' => $item->category,
                'location' => 'Not specified', // Ingredients don't have location
                'supplier' => 'Not specified', // Ingredients don't have supplier
                'description' => $item->description,
                'status' => $item->quantity > ($item->minimum_stock ?? 0) ? 'available' : ($item->quantity <= 0 ? 'out_of_stock' : 'low_stock')
            ]
        ]);
    }

    /**
     * Submit inventory report for a specific item
     */
    public function reportStock(Request $request, $id)
    {
        $request->validate([
            'reported_quantity' => 'required|numeric|min:0',
            'needs_restock' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500'
        ]);

        $item = Ingredient::findOrFail($id);

        // Create inventory check report
        $inventoryCheck = \App\Models\InventoryCheck::create([
            'user_id' => Auth::user()->user_id,
            'notes' => "Stock report for {$item->name}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Add item to the check
        \App\Models\InventoryCheckItem::create([
            'inventory_check_id' => $inventoryCheck->id,
            'inventory_id' => $item->id,
            'current_quantity' => $item->quantity,
            'reported_quantity' => $request->reported_quantity,
            'needs_restock' => $request->needs_restock ?? false,
            'notes' => $request->notes ?? "Stock report: {$request->reported_quantity} {$item->unit}"
        ]);

        // Update ingredient quantity if reported
        $previousQuantity = $item->quantity;
        $item->quantity = $request->reported_quantity;
        $item->updated_by = Auth::user()->user_id;
        $item->save();

        // Send notification to cook
        $notificationService = new \App\Services\NotificationService();
        $notificationService->inventoryReportCreated([
            'id' => $inventoryCheck->id,
            'submitted_by' => Auth::user()->name,
            'items_count' => 1,
            'auto_generated' => false,
            'reason' => 'Kitchen stock report'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock report submitted successfully! Cook has been notified.'
        ]);
    }

    /**
     * Get inventory statistics
     */
    public function getStats()
    {
        $allItems = Ingredient::all();

        $stats = [
            'total_items' => $allItems->count(),
            'available_items' => $allItems->filter(function ($item) {
                return $item->quantity > ($item->minimum_stock ?? 0);
            })->count(),
            'low_stock_items' => $allItems->filter(function ($item) {
                return $item->quantity <= ($item->minimum_stock ?? 0) && $item->quantity > 0;
            })->count(),
            'out_of_stock_items' => $allItems->filter(function ($item) {
                return $item->quantity <= 0;
            })->count(),
            'total_value' => $allItems->sum(function ($item) {
                return $item->quantity * ($item->price ?? 0);
            })
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Add stock to an ingredient (delivery notification)
     */
    public function addStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:500',
            'batch_number' => 'nullable|string|max:100'
        ]);

        $ingredient = Ingredient::findOrFail($id);
        $quantity = $request->quantity;
        $remarks = $request->remarks ?? 'Stock added via delivery';
        $batchNumber = $request->batch_number;

        // Add stock using the model method
        $ingredient->addStock($quantity, Auth::user()->user_id, $remarks);

        // Create a delivery record for tracking
        $deliveryData = [
            'ingredient_id' => $ingredient->id,
            'quantity_delivered' => $quantity,
            'delivered_by' => Auth::user()->user_id,
            'delivered_at' => now(),
            'remarks' => $remarks,
            'batch_number' => $batchNumber,
            'delivery_type' => 'manual_delivery'
        ];

        // You might want to create a Delivery model/table for this
        // For now, we'll log it in the history or create a simple log
        \Log::info('Stock delivery recorded', $deliveryData);

        return response()->json([
            'success' => true,
            'message' => "✅ {$quantity}{$ingredient->unit} of {$ingredient->name} added to inventory.",
            'new_quantity' => $ingredient->fresh()->quantity,
            'status' => $this->getStockStatus($ingredient->fresh())
        ]);
    }

    /**
     * Use/deduct stock from an ingredient
     */
    public function useStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
            'used_by' => 'nullable|string|max:100'
        ]);

        $ingredient = Ingredient::findOrFail($id);
        $quantity = $request->quantity;
        $reason = $request->reason;
        $usedBy = $request->used_by ?? Auth::user()->name;

        // Check if sufficient stock is available
        if ($ingredient->quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "❌ Insufficient stock. Available: {$ingredient->quantity}{$ingredient->unit}, Requested: {$quantity}{$ingredient->unit}"
            ], 400);
        }

        // Use stock using the model method
        $ingredient->useStock($quantity, Auth::user()->user_id, $reason);

        return response()->json([
            'success' => true,
            'message' => "✅ {$quantity}{$ingredient->unit} of {$ingredient->name} deducted from inventory.",
            'new_quantity' => $ingredient->fresh()->quantity,
            'status' => $this->getStockStatus($ingredient->fresh())
        ]);
    }

    /**
     * Get stock status for an ingredient
     */
    private function getStockStatus($ingredient)
    {
        $quantity = $ingredient->quantity;
        $minStock = $ingredient->minimum_stock ?? 0;

        if ($quantity <= 0) {
            return 'out_of_stock';
        } elseif ($quantity <= $minStock) {
            return 'low_stock';
        } else {
            return 'available';
        }
    }

    /**
     * Check inventory levels and create route for kitchen.inventory.check
     */
    public function check(Request $request)
    {
        // Get all ingredients
        $allItems = Ingredient::orderBy('name')->get();

        // Get low stock items
        $lowStockItems = $allItems->filter(function ($item) {
            return $item->quantity <= ($item->minimum_stock ?? 0) && $item->quantity > 0;
        });

        // Get out of stock items
        $outOfStockItems = $allItems->filter(function ($item) {
            return $item->quantity <= 0;
        });

        // Get ingredients that need attention
        $ingredients = Ingredient::orderBy('name')->get();

        return view('kitchen.inventory.check', compact('allItems', 'lowStockItems', 'outOfStockItems', 'ingredients'));
    }
}
