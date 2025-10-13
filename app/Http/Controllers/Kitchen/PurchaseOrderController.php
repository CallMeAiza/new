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

        // Get received orders (delivered status)
        $receivedOrders = PurchaseOrder::with(['creator', 'deliveryConfirmer', 'items.inventoryItem'])
            ->where('status', 'delivered')
            ->orderBy('delivered_at', 'desc')
            ->paginate(15, ['*'], 'received_page')
            ->appends($request->query());

        return view('kitchen.purchase-orders.index', compact('ordersToConfirm', 'receivedOrders'));
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
}
