<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'purchase_order_id',
        'bill_id',
        'supplier_id',
        'payment_category_id',
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
        'payment_category',
        'payee',
        'address',
        'print_later',
        'pay_online',
        'cleared_date',
        'reconciled',
        'reconciled_date',
        'reconciled_by',
        'approval_status',
        'approved_by',
        'approved_at',
        'expense_account_id',
        'bank_account_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'print_later' => 'boolean',
        'pay_online' => 'boolean',
        'cleared_date' => 'date',
        'reconciled' => 'boolean',
        'reconciled_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentCategory(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class);
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
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
            // Update invoice payment totals when payment is created (if invoice exists)
            if ($payment->invoice_id && $payment->invoice) {
                $payment->invoice->calculateTotals();
            }
        });

        static::updated(function ($payment) {
            // Update invoice payment totals when payment is updated (if invoice exists)
            if ($payment->invoice_id && $payment->invoice) {
                $payment->invoice->calculateTotals();
            }
        });

        static::deleted(function ($payment) {
            // Update invoice payment totals when payment is deleted (if invoice exists)
            if ($payment->invoice_id && $payment->invoice) {
                $payment->invoice->calculateTotals();
            }
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
            'voided' => 'red',
            default => 'gray'
        };
    }

    public function getApprovalStatusColorAttribute()
    {
        return match($this->approval_status) {
            'approved' => 'green',
            'pending' => 'yellow',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    public function getClearedStatusAttribute()
    {
        if ($this->reconciled) {
            return 'reconciled';
        } elseif ($this->cleared_date) {
            return 'cleared';
        }
        return 'pending';
    }

    public function getClearedStatusColorAttribute()
    {
        return match($this->cleared_status) {
            'reconciled' => 'green',
            'cleared' => 'blue',
            'pending' => 'yellow',
            default => 'gray'
        };
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('payment_category_id', $categoryId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeReconciled($query)
    {
        return $query->where('reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isReconciled()
    {
        return $this->reconciled;
    }

    public function isCleared()
    {
        return !is_null($this->cleared_date);
    }

    public function canBeVoided()
    {
        return in_array($this->status, ['pending', 'completed']) && !$this->reconciled;
    }

    public function canBeReconciled()
    {
        return $this->status === 'completed' && !$this->reconciled;
    }
}
