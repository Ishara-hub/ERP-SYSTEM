<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    protected $fillable = [
        'bill_id',
        'item_id',
        'purchase_order_item_id',
        'expense_account_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'memo',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($billItem) {
            $billItem->calculateTotals();
        });
    }

    public function calculateTotals()
    {
        // If quantity and unit_price are set, calculate amount from them
        if ($this->quantity && $this->unit_price) {
            $this->amount = $this->quantity * $this->unit_price;
        } elseif (!$this->amount) {
            // If no amount set, default to 0
            $this->amount = 0;
        }
        
        $this->tax_amount = $this->amount * ($this->tax_rate / 100);
        $this->total_amount = $this->amount + $this->tax_amount;
    }
}