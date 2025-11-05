<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'category',
        'price',
        'quantity',
        'description',
        'current_stock',
        'minimum_stock',
        'cost_per_unit',
        'supplier_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
    ];

    /**
     * Get the inventory check items for this ingredient.
     */
    public function inventoryCheckItems(): HasMany
    {
        return $this->hasMany(InventoryCheckItem::class);
    }

    /**
     * Get the purchase order items for this ingredient.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Check if the ingredient needs to be restocked.
     */
    public function needsRestock(): bool
    {
        return $this->quantity < $this->minimum_stock;
    }

    /**
     * Add stock to ingredient
     */
    public function addStock(float $quantity, $updatedBy = null, ?string $reason = null): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $previousQuantity = $this->quantity;
        $this->quantity += $quantity;
        $this->updated_by = $updatedBy ?? $this->updated_by;
        $this->save();

        // Log the stock addition (if logging table exists)
        if (\Schema::hasTable('inventory_history')) {
            // For ingredients, we need to create a separate history table or use a different approach
            // Since ingredients and inventory are separate tables, we'll skip history logging for now
            // TODO: Create a separate ingredients_history table if needed
        }

        return true;
    }

    /**
     * Use/deduct stock from ingredient
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
        $this->updated_by = $updatedBy ?? $this->updated_by;
        $this->save();

        // Log the stock usage (if logging table exists)
        if (\Schema::hasTable('inventory_history')) {
            // For ingredients, we need to create a separate history table or use a different approach
            // Since ingredients and inventory are separate tables, we'll skip history logging for now
            // TODO: Create a separate ingredients_history table if needed
        }

        return true;
    }
}
