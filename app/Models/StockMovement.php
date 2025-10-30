<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'source_document',
        'source_document_id',
        'transaction_date',
        'description',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
