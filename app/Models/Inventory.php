<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'unit',
        'category',
        'reorder_point',
        'supplier',
        'location',
        'unit_price',
        'last_updated_by',
        'status'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'unit_price' => 'decimal:2'
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'inventory_item_id');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by', 'user_id');
    }

    public function checkItems()
    {
        return $this->hasMany(InventoryCheckItem::class, 'ingredient_id');
    }

    public function history()
    {
        return $this->hasMany(InventoryHistory::class, 'inventory_item_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'inventory_id');
    }

    public function mealIngredients()
    {
        return $this->hasMany(MealIngredient::class, 'inventory_id');
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        // Since expiry_date column doesn't exist, return empty query
        return $query->whereRaw('1 = 0'); // Always false
    }

    // Helper methods
    public function isLowStock()
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    public function isExpiringSoon($days = 7)
    {
        // Since expiry_date column doesn't exist, always return false
        return false;
    }

    public function getStatusAttribute($value)
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } elseif ($this->isExpiringSoon()) {
            return 'expired';
        }
        return 'available';
    }

    /**
     * Add stock to inventory
     */
    public function addStock(float $quantity, $updatedBy = null, ?string $reason = null): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $previousQuantity = $this->quantity;
        $this->quantity += $quantity;
        $this->last_updated_by = $updatedBy ?? $this->last_updated_by;
        $this->save();

        // Log the stock addition
        InventoryHistory::create([
            'inventory_item_id' => $this->id,
            'user_id' => $updatedBy ?? $this->last_updated_by,
            'action_type' => 'stock_added',
            'quantity_change' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $this->quantity,
            'notes' => $reason ?? 'Stock added to inventory'
        ]);

        return true;
    }

    /**
     * Use/deduct stock from inventory
     */
    public function useStock(float $quantity, $updatedBy = null, ?string $reason = null): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        if ($this->quantity < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$this->quantity}, Requested: {$quantity}");
        }

        $previousQuantity = $this->quantity;
        $this->quantity -= $quantity;
        $this->last_updated_by = $updatedBy ?? $this->last_updated_by;
        $this->save();

        // Log the stock usage
        InventoryHistory::create([
            'inventory_item_id' => $this->id,
            'user_id' => $updatedBy ?? $this->last_updated_by,
            'action_type' => 'stock_used',
            'quantity_change' => -$quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $this->quantity,
            'notes' => $reason ?? 'Stock used from inventory'
        ]);

        return true;
    }
}
