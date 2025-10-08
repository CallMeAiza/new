<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'created_by',
        'supplier_name',
        'status',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'total_amount',
        'notes',
        'approved_by',
        'approved_at',
        'delivered_by',
        'delivered_at'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    /**
     * Boot method to generate order number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($purchaseOrder) {
            if (empty($purchaseOrder->order_number)) {
                // Get the last order number for current year
                $lastOrder = static::whereYear('created_at', date('Y'))
                    ->orderBy('id', 'desc')
                    ->first();
                
                $nextNumber = 1;
                if ($lastOrder && $lastOrder->order_number) {
                    // Extract number from last order (e.g., PO-2025-0006 -> 6)
                    preg_match('/PO-\d{4}-(\d+)/', $lastOrder->order_number, $matches);
                    if (isset($matches[1])) {
                        $nextNumber = intval($matches[1]) + 1;
                    }
                }
                
                $purchaseOrder->order_number = 'PO-' . date('Y') . '-' . str_pad(
                    $nextNumber,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Get the user who created this purchase order
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    // Supplier relationship removed as requested - focusing only on items

    /**
     * Get the user who approved this purchase order
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Get the user who confirmed delivery
     */
    public function deliveryConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by', 'user_id');
    }

    /**
     * Get the items for this purchase order
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Helper methods
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeDelivered()
    {
        return $this->status === 'approved' || $this->status === 'ordered';
    }

    /**
     * Approve the purchase order
     */
    public function approve($approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now()
        ]);
    }

    /**
     * Mark as delivered and update inventory
     */
    public function markAsDelivered($deliveredBy, $deliveryDate = null)
    {
        $this->update([
            'status' => 'delivered',
            'delivered_by' => $deliveredBy,
            'delivered_at' => now(),
            'actual_delivery_date' => $deliveryDate ?? now()->toDateString()
        ]);

        // Update inventory quantities
        $this->updateInventoryFromDelivery();
    }

    /**
     * Update inventory quantities when order is delivered
     */
    protected function updateInventoryFromDelivery()
    {
        foreach ($this->items as $item) {
            $inventoryItem = $item->inventoryItem;
            if ($inventoryItem) {
                $previousQuantity = $inventoryItem->quantity;
                $inventoryItem->quantity += $item->quantity_delivered ?: $item->quantity_ordered;
                $inventoryItem->last_updated_by = $this->delivered_by;
                $inventoryItem->save();

                // Log inventory history
                InventoryHistory::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => $this->delivered_by,
                    'action_type' => 'purchase_delivery',
                    'quantity_change' => $item->quantity_delivered ?: $item->quantity_ordered,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $inventoryItem->quantity,
                    'notes' => "Purchase Order {$this->order_number} delivery"
                ]);
            }
        }
    }

    /**
     * Calculate total amount from items
     */
    public function calculateTotal()
    {
        $total = $this->items->sum('total_price');
        $this->update(['total_amount' => $total]);
        return $total;
    }
}
