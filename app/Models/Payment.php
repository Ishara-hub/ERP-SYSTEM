<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_number',
        'payment_date',
        'payment_method',
        'amount',
        'reference',
        'notes',
        'status',
        'bank_name',
        'check_number',
        'transaction_id',
        'fee_amount',
        'received_by',
        'payment_type',
        'payee',
        'address',
        'print_later',
        'pay_online',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'print_later' => 'boolean',
        'pay_online' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber();
            }
        });

        static::created(function ($payment) {
            // Update invoice payment totals when payment is created
            $payment->invoice->calculateTotals();
        });

        static::updated(function ($payment) {
            // Update invoice payment totals when payment is updated
            $payment->invoice->calculateTotals();
        });

        static::deleted(function ($payment) {
            // Update invoice payment totals when payment is deleted
            $payment->invoice->calculateTotals();
        });
    }

    public static function generatePaymentNumber()
    {
        $lastPayment = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastPayment ? $lastPayment->id + 1 : 1;
        return 'PAY-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getNetAmountAttribute()
    {
        return $this->amount - $this->fee_amount;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }
}
