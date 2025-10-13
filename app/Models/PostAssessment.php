<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'meal_type',
        'menu_id',
        'planned_portions',
        'actual_portions_served',
        'leftover_portions',
        'food_waste_kg',
        'cost_per_portion',
        'total_cost',
        'student_satisfaction_avg',
        'notes',
        'improvements',
        'image_path',
        'image_paths',
        'items',
        'is_completed',
        'assessed_by',
        'reported_by',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'planned_portions' => 'integer',
        'actual_portions_served' => 'integer',
        'leftover_portions' => 'integer',
        'food_waste_kg' => 'decimal:2',
        'cost_per_portion' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'student_satisfaction_avg' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'items' => 'array',
        'image_paths' => 'array',
    ];

    /**
     * Get the user that created the assessment.
     */
    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by', 'user_id');
    }

    /**
     * Get the menu item for this assessment.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
