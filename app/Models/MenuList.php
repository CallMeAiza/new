<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuList extends Model
{
    use HasFactory;

    protected $table = 'menu_list';

    protected $fillable = [
        'name',
        'ingredients',
        'prep_time',
        'cooking_time',
        'serving_size',
        'meal_type',
        'day_of_week',
        'week_cycle',
    ];

    protected $casts = [
        'ingredients' => 'array',
        'prep_time' => 'integer',
        'cooking_time' => 'integer',
        'serving_size' => 'integer',
        'week_cycle' => 'integer',
    ];
}
