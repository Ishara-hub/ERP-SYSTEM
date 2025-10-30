<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_number',
        'customer_id',
        'quotation_date',
        'valid_until',
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
        'quotation_date' => 'date',
        'valid_until' => 'date',
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
        return $this->hasMany(QuotationLineItem::class);
    }

    public static function generateQuotationNumber(): string
    {
        $lastQuotation = self::orderBy('id', 'desc')->first();
        $number = $lastQuotation ? $lastQuotation->id + 1 : 1;
        return 'QT-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
