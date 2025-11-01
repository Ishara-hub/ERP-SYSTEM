<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'bill_number',
        'supplier_id',
        'purchase_order_id',
        'liability_account_id',
        'bill_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'status',
        'memo',
        'terms',
        'reference',
        'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function liabilityAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'liability_account_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bill_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            if (empty($bill->bill_number)) {
                $bill->bill_number = self::generateBillNumber();
            }
        });

        static::saving(function ($bill) {
            $bill->calculateTotals();
        });
    }

    public static function generateBillNumber()
    {
        $lastBill = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastBill ? $lastBill->id + 1 : 1;
        return 'BILL-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $subtotal = $this->items->sum('amount');
        $taxAmount = $this->items->sum('tax_amount');
        $totalAmount = $subtotal + $taxAmount;
        $balanceDue = $totalAmount - $this->paid_amount;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $totalAmount;
        $this->balance_due = $balanceDue;

        // Update status based on balance
        if ($balanceDue <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date && $this->due_date < now()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'pending';
        }
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'yellow',
            'partial' => 'blue',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->paid_amount ?? 0;
    }

    public function isPaid()
    {
        return $this->balance_due <= 0;
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date < now() && !$this->isPaid();
    }

    public function canBePaid()
    {
        return in_array($this->status, ['pending', 'partial', 'overdue']);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByLiabilityAccount($query, $accountId)
    {
        return $query->where('liability_account_id', $accountId);
    }
}