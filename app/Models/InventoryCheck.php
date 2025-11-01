<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'check_date',
        'notes',
        'approved_at',
        'approved_by',
        'approval_notes',
        'status'
    ];

    protected $casts = [
        'check_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that performed the inventory check.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the inventory check items for this check.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InventoryCheckItem::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }
}
