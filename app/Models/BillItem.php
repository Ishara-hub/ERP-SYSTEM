<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    protected $fillable = [
        'bill_id',
        'expense_account_id',
        'description',
        'amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'memo',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
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
        $this->tax_amount = $this->amount * ($this->tax_rate / 100);
        $this->total_amount = $this->amount + $this->tax_amount;
    }
}