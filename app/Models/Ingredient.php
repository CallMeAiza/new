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
        return $this->current_stock < $this->minimum_stock;
    }
}
