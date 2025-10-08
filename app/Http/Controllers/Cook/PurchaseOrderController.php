<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Inventory;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    /**
     * Display purchase orders list
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['creator', 'approver', 'deliveryConfirmer', 'items.inventoryItem']);

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('order_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('order_date', '<=', $request->date_to);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        // Get statistics
        $stats = [
            'pending_orders' => PurchaseOrder::pending()->count(),
            'approved_orders' => PurchaseOrder::approved()->count(),
            'cancelled_orders' => PurchaseOrder::where('status', 'cancelled')->count(),
            'delivered_orders' => PurchaseOrder::delivered()->count()
        ];

        return view('cook.purchase-orders.index', compact('purchaseOrders', 'stats'));
    }

    /**
     * Show create purchase order form
     */
    public function create()
    {
        // Get low stock items that need reordering
        $lowStockItems = Inventory::lowStock()->get();
        $allItems = Inventory::orderBy('name')->get();

        return view('cook.purchase-orders.create', compact('lowStockItems', 'allItems'));
    }

    /**
     * Store new purchase order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'supplier_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'created_by' => Auth::user()->user_id,
                'supplier_name' => $request->supplier_name,
                'status' => 'pending',
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes
            ]);

            // Create purchase order items
            $totalAmount = 0;
            foreach ($request->items as $itemData) {
                // Try to find existing inventory item by name
                $inventoryItem = Inventory::where('name', $itemData['name'])->first();
                
                $item = PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'inventory_id' => $inventoryItem ? $inventoryItem->id : null,
                    'item_name' => $itemData['name'],
                    'quantity_ordered' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null
                ]);

                $totalAmount += $item->total_price;
            }

            // Update total amount
            $purchaseOrder->update(['total_amount' => $totalAmount]);

            // Send notification to kitchen staff
            $notificationService = new NotificationService();
            $notificationService->purchaseOrderCreated($purchaseOrder);

            DB::commit();

            return redirect()->route('cook.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to create purchase order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show purchase order details
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['creator', 'approver', 'deliveryConfirmer', 'items.inventoryItem']);
        
        return view('cook.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show edit form
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be edited.');
        }

        return view('cook.purchase-orders.edit', compact('purchaseOrder'));
    }

    /**
     * Update purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be edited.');
        }

        $validator = Validator::make($request->all(), [
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update purchase order
            $purchaseOrder->update([
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes
            ]);

            // Delete old items
            $purchaseOrder->items()->delete();

            // Create new items
            $totalAmount = 0;
            foreach ($request->items as $itemData) {
                $inventoryItem = Inventory::where('name', $itemData['name'])->first();
                
                $item = PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'inventory_id' => $inventoryItem ? $inventoryItem->id : null,
                    'item_name' => $itemData['name'],
                    'quantity_ordered' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null
                ]);

                $totalAmount += $item->total_price;
            }

            // Update total amount
            $purchaseOrder->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('cook.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update purchase order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Approve purchase order
     */
    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeApproved()) {
            return redirect()->back()->with('error', 'Purchase order cannot be approved.');
        }

        $purchaseOrder->approve(Auth::user()->user_id);

        // Send notification to kitchen staff
        $notificationService = new NotificationService();
        $notificationService->purchaseOrderApproved($purchaseOrder);

        return redirect()->back()->with('success', 'Purchase order approved successfully!');
    }

    /**
     * Cancel purchase order
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'delivered') {
            return redirect()->back()->with('error', 'Cannot cancel delivered purchase order.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Purchase order cancelled successfully!');
    }

    /**
     * Delete purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'delivered') {
            return redirect()->back()->with('error', 'Cannot delete delivered purchase order.');
        }

        if ($purchaseOrder->status === 'approved') {
            return redirect()->back()->with('error', 'Cannot delete ordered purchase order.');
        }

        DB::beginTransaction();
        try {
            // Delete all items first
            $purchaseOrder->items()->delete();
            
            // Delete the purchase order
            $purchaseOrder->delete();

            DB::commit();

            return redirect()->route('cook.purchase-orders.index')
                ->with('success', 'Purchase order deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Order Again - Duplicate an ordered purchase order as pending
     */
    public function orderAgain(PurchaseOrder $purchaseOrder)
    {
        // Only allow ordering again from approved/ordered status
        if ($purchaseOrder->status !== 'approved') {
            return redirect()->back()->with('error', 'Can only reorder from ordered purchase orders.');
        }

        DB::beginTransaction();
        try {
            // Create new purchase order with same data but pending status
            $newOrder = PurchaseOrder::create([
                'created_by' => Auth::user()->user_id,
                'supplier_name' => $purchaseOrder->supplier_name,
                'status' => 'pending',
                'order_date' => now()->toDateString(),
                'expected_delivery_date' => $purchaseOrder->expected_delivery_date,
                'notes' => $purchaseOrder->notes,
                'total_amount' => 0 // Will be calculated after items are added
            ]);

            // Copy all items from original order
            $totalAmount = 0;
            foreach ($purchaseOrder->items as $item) {
                $newItem = $newOrder->items()->create([
                    'item_name' => $item->item_name,
                    'quantity_ordered' => $item->quantity_ordered,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'inventory_item_id' => $item->inventory_item_id
                ]);
                $totalAmount += $item->total_price;
            }

            // Update total amount
            $newOrder->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('cook.purchase-orders.show', $newOrder)
                ->with('success', 'New purchase order created successfully! You can now edit it before ordering.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create new order: ' . $e->getMessage());
        }
    }

    /**
     * Download purchase order receipt
     */
    public function download(PurchaseOrder $purchaseOrder, Request $request)
    {
        $format = $request->get('format', 'pdf');
        $purchaseOrder->load(['creator', 'items']);

        if ($format === 'pdf') {
            return $this->downloadPDF($purchaseOrder);
        } elseif ($format === 'word') {
            return $this->downloadWord($purchaseOrder);
        }

        return redirect()->back()->with('error', 'Invalid format selected');
    }

    /**
     * Download receipt as PDF (HTML)
     */
    private function downloadPDF($purchaseOrder)
    {
        $filename = 'PO-' . $purchaseOrder->order_number . '.html';
        
        $headers = [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $html = view('cook.purchase-orders.receipt-pdf', compact('purchaseOrder'))->render();

        return response($html, 200, $headers);
    }

    /**
     * Download as Word Document
     */
    private function downloadWord($purchaseOrder)
    {
        $filename = 'PO-' . $purchaseOrder->order_number . '.doc';
        
        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $html = view('cook.purchase-orders.receipt-word', compact('purchaseOrder'))->render();

        return response($html, 200, $headers);
    }

    /**
     * Get low stock items for AJAX
     */
    public function getLowStockItems()
    {
        $lowStockItems = Inventory::lowStock()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'current_quantity' => $item->quantity,
                    'reorder_point' => $item->reorder_point,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'shortage' => max(0, $item->reorder_point - $item->quantity)
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $lowStockItems
        ]);
    }

    /**
     * Generate purchase order suggestions based on low stock
     */
    public function generateSuggestions()
    {
        $suggestions = Inventory::lowStock()
            ->get()
            ->map(function ($item) {
                $suggestedQuantity = max(
                    $item->reorder_point * 2, // Order double the reorder point
                    $item->reorder_point - $item->quantity + 10 // Or shortage plus buffer
                );

                return [
                    'inventory_id' => $item->id,
                    'name' => $item->name,
                    'current_stock' => $item->quantity,
                    'reorder_point' => $item->reorder_point,
                    'suggested_quantity' => $suggestedQuantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'estimated_cost' => $suggestedQuantity * $item->unit_price
                ];
            });

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }
}
