<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderLineItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'item_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($lineItem) {
            $lineItem->amount = $lineItem->quantity * $lineItem->unit_price;
            $lineItem->tax_amount = $lineItem->amount * ($lineItem->tax_rate / 100);
            $lineItem->discount_amount = $lineItem->amount * ($lineItem->discount_rate / 100);
        });
    }
}
