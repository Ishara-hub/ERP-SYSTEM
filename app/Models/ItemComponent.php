<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemComponent extends Model
{
    protected $fillable = [
        'assembly_item_id', 'component_item_id', 'quantity', 'unit_cost', 'total_cost', 'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    // Relationships
    public function assemblyItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'assembly_item_id');
    }

    public function componentItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }

    // Accessors
    public function getFormattedUnitCostAttribute(): string
    {
        return '$' . number_format($this->unit_cost, 2);
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return '$' . number_format($this->total_cost, 2);
    }

    // Methods
    public function calculateTotalCost(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function updateTotalCost(): void
    {
        $this->total_cost = $this->calculateTotalCost();
        $this->save();
    }
}