<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'name',
        'ingredients',
        'prep_time',
        'cooking_time',
        'serving_size',
        'meal_type',
        'day_of_week',
        'week_cycle'
    ];

    protected $casts = [
        'ingredients' => 'array',
        'prep_time' => 'integer',
        'cooking_time' => 'integer',
        'serving_size' => 'integer',
        'week_cycle' => 'integer'
    ];

    public function polls()
    {
        return $this->hasMany(KitchenMenuPoll::class, 'meal_id');
    }

    public function scopeForWeekCycle($query, $weekCycle)
    {
        return $query->where('week_cycle', $weekCycle);
    }

    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }

    public function scopeForMealType($query, $mealType)
    {
        return $query->where('meal_type', strtolower($mealType));
    }

    /**
     * Get the ingredients for this meal
     */
    public function mealIngredients()
    {
        return $this->hasMany(MealIngredient::class);
    }

    /**
     * Get the inventory items through meal ingredients
     */
    public function inventoryItems()
    {
        return $this->belongsToMany(Inventory::class, 'meal_ingredients', 'meal_id', 'inventory_id')
                    ->withPivot('quantity_per_serving', 'unit')
                    ->withTimestamps();
    }

    /**
     * Check if meal can be prepared with current inventory
     */
    public function canBePrepared($servings = null)
    {
        $servings = $servings ?: $this->serving_size;

        foreach ($this->mealIngredients as $ingredient) {
            $requiredQuantity = $ingredient->calculateTotalQuantity($servings);
            if ($ingredient->inventoryItem->quantity < $requiredQuantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing ingredients for preparation
     */
    public function getMissingIngredients($servings = null)
    {
        $servings = $servings ?: $this->serving_size;
        $missing = [];

        foreach ($this->mealIngredients as $ingredient) {
            $requiredQuantity = $ingredient->calculateTotalQuantity($servings);
            $availableQuantity = $ingredient->inventoryItem->quantity;

            if ($availableQuantity < $requiredQuantity) {
                $missing[] = [
                    'name' => $ingredient->inventoryItem->name,
                    'required' => $requiredQuantity,
                    'available' => $availableQuantity,
                    'shortage' => $requiredQuantity - $availableQuantity,
                    'unit' => $ingredient->unit
                ];
            }
        }

        return $missing;
    }

    /**
     * Deduct ingredients from inventory when meal is prepared
     */
    public function deductIngredients($servings = null, $userId = null)
    {
        $servings = $servings ?: $this->serving_size;
        $userId = $userId ?? (auth()->user()->user_id ?? 'system');

        foreach ($this->mealIngredients as $ingredient) {
            $requiredQuantity = $ingredient->calculateTotalQuantity($servings);
            $inventoryItem = $ingredient->inventoryItem;

            if ($inventoryItem->quantity >= $requiredQuantity) {
                // Use the new useStock method for consistent stock management
                try {
                    $inventoryItem->useStock(
                        $requiredQuantity,
                        $userId,
                        "Used for meal: {$this->name} ({$servings} servings)"
                    );
                } catch (\Exception $e) {
                    // Log error but continue with other ingredients
                    \Log::error("Failed to deduct stock for {$inventoryItem->name}: " . $e->getMessage());
                    throw $e; // Re-throw to maintain existing behavior
                }
            } else {
                throw new \Exception("Insufficient stock for {$inventoryItem->name}. Required: {$requiredQuantity}, Available: {$inventoryItem->quantity}");
            }
        }
    }

    public function getCurrentPoll($date)
    {
        return $this->polls()
            ->where('poll_date', $date)
            ->where('status', 'active')
            ->first();
    }
} 