<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DeliveryDraft;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    /**
     * Display purchase orders for kitchen staff
     */
    public function index(Request $request)
    {
        // Get orders that need confirmation (approved/ordered status)
        $ordersToConfirm = PurchaseOrder::with(['creator', 'approver', 'items.inventoryItem'])
            ->whereIn('status', ['approved', 'ordered'])
            ->orderBy('expected_delivery_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get received orders (delivered status) - excluding outside purchases
        $receivedOrders = PurchaseOrder::with(['creator', 'deliveryConfirmer', 'items.inventoryItem'])
            ->where('status', 'delivered')
            ->where(function($query) {
                $query->whereNull('notes')
                      ->orWhere('notes', 'NOT LIKE', 'OUTSIDE PURCHASE:%');
            })
            ->orderBy('delivered_at', 'desc')
            ->paginate(15, ['*'], 'received_page')
            ->appends($request->query());

        // Get outside purchase orders (not submitted)
        $outsidePurchases = PurchaseOrder::with(['creator', 'deliveryConfirmer', 'items.inventoryItem'])
            ->where('status', 'delivered')
            ->where('notes', 'LIKE', 'OUTSIDE PURCHASE:%')
            ->where('notes', 'NOT LIKE', '%[SUBMITTED]%')
            ->orderBy('delivered_at', 'desc')
            ->paginate(10, ['*'], 'outside_page')
            ->appends($request->query());

        // Get submitted outside purchase orders
        $submittedOutsidePurchases = PurchaseOrder::with(['creator', 'deliveryConfirmer', 'items.inventoryItem'])
            ->where('status', 'delivered')
            ->where('notes', 'LIKE', 'OUTSIDE PURCHASE:%')
            ->where('notes', 'LIKE', '%[SUBMITTED]%')
            ->orderBy('delivered_at', 'desc')
            ->paginate(10, ['*'], 'submitted_page')
            ->appends($request->query());

        return view('kitchen.purchase-orders.index', compact('ordersToConfirm', 'receivedOrders', 'outsidePurchases', 'submittedOutsidePurchases'));
    }

    /**
     * Show purchase order details for kitchen staff
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['creator', 'approver', 'deliveryConfirmer', 'items.inventoryItem']);
        
        return view('kitchen.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show delivery confirmation form
     */
    public function confirmDelivery(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeDelivered()) {
            return redirect()->back()->with('error', 'This purchase order cannot be marked as delivered.');
        }

        $purchaseOrder->load(['items.inventoryItem']);
        
        // Get saved draft if exists
        $draft = DeliveryDraft::where('purchase_order_id', $purchaseOrder->id)
            ->where('user_id', Auth::user()->user_id)
            ->first();
        
        return view('kitchen.purchase-orders.confirm-delivery', compact('purchaseOrder', 'draft'));
    }

    /**
     * Process delivery confirmation
     */
    public function processDelivery(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeDelivered()) {
            return redirect()->back()->with('error', 'This purchase order cannot be marked as delivered.');
        }

        $validator = Validator::make($request->all(), [
            'actual_delivery_date' => 'required|date',
            'delivery_notes' => 'nullable|string|max:1000',
            'receiver_name' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_delivered' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update purchase order items with delivered quantities
            foreach ($request->items as $itemData) {
                $item = PurchaseOrderItem::find($itemData['id']);
                $item->update([
                    'quantity_delivered' => $itemData['quantity_delivered'],
                    'notes' => $itemData['notes'] ?? $item->notes
                ]);
            }

            // Mark purchase order as delivered
            $purchaseOrder->markAsDelivered(
                Auth::user()->user_id,
                $request->actual_delivery_date
            );

            // Update notes and receiver name
            $notesUpdate = $purchaseOrder->notes;
            if ($request->delivery_notes) {
                $notesUpdate .= "\n\nDelivery Notes: " . $request->delivery_notes;
            }
            
            $purchaseOrder->update([
                'notes' => $notesUpdate,
                'received_by_name' => $request->receiver_name
            ]);

            // Send notification to cook
            $notificationService = new NotificationService();
            $notificationService->purchaseOrderDelivered($purchaseOrder);

            // Delete draft if exists
            DeliveryDraft::where('purchase_order_id', $purchaseOrder->id)
                ->where('user_id', Auth::user()->user_id)
                ->delete();

            DB::commit();

            return redirect()->route('kitchen.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Delivery confirmed successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to confirm delivery: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get pending deliveries for dashboard
     */
    public function getPendingDeliveries()
    {
        $pendingDeliveries = PurchaseOrder::whereIn('status', ['approved', 'ordered'])
            ->with(['creator', 'items.inventoryItem'])
            ->orderBy('expected_delivery_date')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'created_by' => $order->creator->user_fname . ' ' . $order->creator->user_lname,
                    'order_date' => $order->order_date->format('M d, Y'),
                    'expected_delivery' => $order->expected_delivery_date ? 
                                         $order->expected_delivery_date->format('M d, Y') : 'Not set',
                    'total_amount' => $order->total_amount,
                    'items_count' => $order->items->count(),
                    'status' => $order->status,
                    'is_overdue' => $order->expected_delivery_date && 
                                   $order->expected_delivery_date->isPast()
                ];
            });

        return response()->json([
            'success' => true,
            'deliveries' => $pendingDeliveries
        ]);
    }

    /**
     * Quick delivery confirmation (for simple cases)
     */
    public function quickConfirmDelivery(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeDelivered()) {
            return response()->json([
                'success' => false,
                'message' => 'This purchase order cannot be marked as delivered.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Mark all items as fully delivered
            foreach ($purchaseOrder->items as $item) {
                $item->update(['quantity_delivered' => $item->quantity_ordered]);
            }

            // Mark purchase order as delivered
            $purchaseOrder->markAsDelivered(Auth::user()->user_id);

            // Send notification to cook
            $notificationService = new NotificationService();
            $notificationService->purchaseOrderDelivered($purchaseOrder);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery confirmed successfully! Inventory has been updated.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save delivery draft
     */
    public function saveDeliveryDraft(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            $validator = Validator::make($request->all(), [
                'actual_delivery_date' => 'nullable|date',
                'delivery_notes' => 'nullable|string',
                'receiver_name' => 'nullable|string',
                'items' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data provided.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Save or update draft
            DeliveryDraft::updateOrCreate(
                [
                    'purchase_order_id' => $purchaseOrder->id,
                    'user_id' => Auth::user()->user_id,
                ],
                [
                    'draft_data' => $request->all(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Changes saved successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save changes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get delivery draft
     */
    public function getDeliveryDraft(PurchaseOrder $purchaseOrder)
    {
        try {
            $draft = DeliveryDraft::where('purchase_order_id', $purchaseOrder->id)
                ->where('user_id', Auth::user()->user_id)
                ->first();

            if ($draft) {
                return response()->json([
                    'success' => true,
                    'draft' => $draft->draft_data
                ]);
            }

            return response()->json([
                'success' => true,
                'draft' => null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve draft: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        try {
            // Only allow deletion of delivered orders
            if ($purchaseOrder->status !== 'delivered') {
                return redirect()->back()->with('error', 'Only delivered purchase orders can be deleted.');
            }

            DB::beginTransaction();

            // Delete the purchase order (items will be cascade deleted)
            $purchaseOrder->delete();

            DB::commit();

            return redirect()->route('kitchen.purchase-orders.index')
                ->with('success', 'Purchase order deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Show form to create outside purchase
     */
    public function createOutside()
    {
        return view('kitchen.purchase-orders.create-outside');
    }

    /**
     * Show form to edit outside purchase
     */
    public function editOutside(PurchaseOrder $purchaseOrder)
    {
        // Verify it's an outside purchase
        if (!$purchaseOrder->notes || !str_starts_with($purchaseOrder->notes, 'OUTSIDE PURCHASE:')) {
            return redirect()->route('kitchen.purchase-orders.index')
                ->with('error', 'This is not an outside purchase.');
        }

        $purchaseOrder->load('items.inventoryItem');
        return view('kitchen.purchase-orders.edit-outside', compact('purchaseOrder'));
    }

    /**
     * Update outside purchase
     */
    public function updateOutside(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Verify it's an outside purchase
        if (!$purchaseOrder->notes || !str_starts_with($purchaseOrder->notes, 'OUTSIDE PURCHASE:')) {
            return redirect()->route('kitchen.purchase-orders.index')
                ->with('error', 'This is not an outside purchase.');
        }

        $validator = Validator::make($request->all(), [
            'purchase_date' => 'required|date|before_or_equal:today',
            'store_name' => 'required|string|max:255',
            'purchased_by' => 'required|string|max:255',
            'total_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Reverse inventory changes from old items
            foreach ($purchaseOrder->items as $oldItem) {
                if ($oldItem->inventory_id) {
                    $inventoryItem = \App\Models\Inventory::find($oldItem->inventory_id);
                    if ($inventoryItem) {
                        $inventoryItem->quantity -= $oldItem->quantity_delivered;
                        $inventoryItem->save();
                    }
                }
            }

            // Delete old items
            $purchaseOrder->items()->delete();

            // Update purchase order
            $purchaseOrder->update([
                'ordered_by' => $request->purchased_by,
                'supplier_name' => $request->store_name,
                'order_date' => $request->purchase_date,
                'expected_delivery_date' => $request->purchase_date,
                'actual_delivery_date' => $request->purchase_date,
                'total_amount' => $request->total_amount ?? 0,
                'notes' => 'OUTSIDE PURCHASE: ' . ($request->notes ?? 'Items purchased from external store'),
                'received_by_name' => $request->purchased_by
            ]);

            // Create new purchase order items and update inventory
            foreach ($request->items as $itemData) {
                // Try to find matching inventory item by name
                $inventoryItem = \App\Models\Inventory::where('name', 'LIKE', '%' . $itemData['name'] . '%')->first();
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'inventory_id' => $inventoryItem ? $inventoryItem->id : null,
                    'item_name' => $itemData['name'],
                    'quantity_ordered' => $itemData['quantity'],
                    'quantity_delivered' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['price'] ?? 0,
                    'total_price' => ($itemData['quantity'] * ($itemData['price'] ?? 0)),
                    'notes' => 'Outside purchase'
                ]);

                // Update inventory if matching item found
                if ($inventoryItem) {
                    $previousQuantity = $inventoryItem->quantity;
                    $inventoryItem->quantity += $itemData['quantity'];
                    $inventoryItem->last_updated_by = Auth::user()->user_id;
                    $inventoryItem->save();

                    // Log inventory history
                    \App\Models\InventoryHistory::create([
                        'inventory_item_id' => $inventoryItem->id,
                        'user_id' => Auth::user()->user_id,
                        'action_type' => 'outside_purchase_update',
                        'quantity_change' => $itemData['quantity'],
                        'previous_quantity' => $previousQuantity,
                        'new_quantity' => $inventoryItem->quantity,
                        'notes' => "Outside purchase updated from {$request->store_name} - PO: {$purchaseOrder->order_number}"
                    ]);
                }
            }

            // Calculate total if not provided
            if (!$request->total_amount) {
                $purchaseOrder->calculateTotal();
            }

            DB::commit();

            return redirect()->route('kitchen.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Outside purchase updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to update outside purchase: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Submit outside purchase to cook
     */
    public function submitOutside(PurchaseOrder $purchaseOrder)
    {
        // Verify it's an outside purchase
        if (!$purchaseOrder->notes || !str_starts_with($purchaseOrder->notes, 'OUTSIDE PURCHASE:')) {
            return redirect()->route('kitchen.purchase-orders.index')
                ->with('error', 'This is not an outside purchase.');
        }

        try {
            // Update status to 'submitted' by adding a flag in notes
            $currentNotes = $purchaseOrder->notes;
            if (!str_contains($currentNotes, '[SUBMITTED]')) {
                $purchaseOrder->notes = $currentNotes . ' [SUBMITTED]';
                $purchaseOrder->save();
            }

            // Send notification to cook
            $notificationService = new NotificationService();
            $notificationService->purchaseOrderDelivered($purchaseOrder);

            return redirect()->route('kitchen.purchase-orders.index')
                ->with('success', 'Outside purchase submitted to cook successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to submit outside purchase: ' . $e->getMessage());
        }
    }

    /**
     * Store outside purchase
     */
    public function storeOutside(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_date' => 'required|date|before_or_equal:today',
            'store_name' => 'required|string|max:255',
            'purchased_by' => 'required|string|max:255',
            'total_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.price' => 'nullable|numeric|min:0',
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
                'ordered_by' => $request->purchased_by,
                'supplier_name' => $request->store_name,
                'status' => 'delivered', // Immediately mark as delivered since it's already purchased
                'order_date' => $request->purchase_date,
                'expected_delivery_date' => $request->purchase_date,
                'actual_delivery_date' => $request->purchase_date,
                'total_amount' => $request->total_amount ?? 0,
                'notes' => 'OUTSIDE PURCHASE: ' . ($request->notes ?? 'Items purchased from external store'),
                'approved_by' => Auth::user()->user_id,
                'approved_at' => now(),
                'delivered_by' => Auth::user()->user_id,
                'delivered_at' => now(),
                'received_by_name' => $request->purchased_by
            ]);

            // Create purchase order items and update inventory
            foreach ($request->items as $itemData) {
                // Try to find matching inventory item by name
                $inventoryItem = \App\Models\Inventory::where('name', 'LIKE', '%' . $itemData['name'] . '%')->first();
                
                $purchaseOrderItem = PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'inventory_id' => $inventoryItem ? $inventoryItem->id : null,
                    'item_name' => $itemData['name'],
                    'quantity_ordered' => $itemData['quantity'],
                    'quantity_delivered' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['price'] ?? 0,
                    'total_price' => ($itemData['quantity'] * ($itemData['price'] ?? 0)),
                    'notes' => 'Outside purchase'
                ]);

                // Update inventory if matching item found
                if ($inventoryItem) {
                    $previousQuantity = $inventoryItem->quantity;
                    $inventoryItem->quantity += $itemData['quantity'];
                    $inventoryItem->last_updated_by = Auth::user()->user_id;
                    $inventoryItem->save();

                    // Log inventory history
                    \App\Models\InventoryHistory::create([
                        'inventory_item_id' => $inventoryItem->id,
                        'user_id' => Auth::user()->user_id,
                        'action_type' => 'outside_purchase',
                        'quantity_change' => $itemData['quantity'],
                        'previous_quantity' => $previousQuantity,
                        'new_quantity' => $inventoryItem->quantity,
                        'notes' => "Outside purchase from {$request->store_name} - PO: {$purchaseOrder->order_number}"
                    ]);
                }
            }

            // Calculate total if not provided
            if (!$request->total_amount) {
                $purchaseOrder->calculateTotal();
            }

            // Send notification to cook
            $notificationService = new NotificationService();
            $notificationService->purchaseOrderDelivered($purchaseOrder);

            DB::commit();

            return redirect()->route('kitchen.purchase-orders.index')
                ->with('success', 'Outside purchase reported successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to report outside purchase: ' . $e->getMessage())
                ->withInput();
        }
    }
}
