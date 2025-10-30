<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'delivery_date',
        'status',
        'payment_terms',
        'shipping_method',
        'shipping_address',
        'billing_address',
        'notes',
        'terms_conditions',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(SalesOrderLineItem::class);
    }

    public static function generateOrderNumber(): string
    {
        $lastOrder = self::orderBy('id', 'desc')->first();
        $number = $lastOrder ? $lastOrder->id + 1 : 1;
        return 'SO-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
