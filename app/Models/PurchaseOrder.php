<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'status',
        'shipping_address',
        'billing_address',
        'terms',
        'reference',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseOrder) {
            if (empty($purchaseOrder->po_number)) {
                $purchaseOrder->po_number = self::generatePONumber();
            }
        });

        static::saving(function ($purchaseOrder) {
            // Calculate totals
            $purchaseOrder->calculateTotals();
        });
    }

    public static function generatePONumber()
    {
        $lastPO = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastPO ? $lastPO->id + 1 : 1;
        return 'PO-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $subtotal = $this->items->sum('amount');
        $taxAmount = $this->items->sum('tax_amount');
        $total = $subtotal + $taxAmount + $this->shipping_amount - $this->discount_amount;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $total;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'confirmed' => 'green',
            'partial' => 'yellow',
            'received' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->status === 'received') return 100;
        if ($this->status === 'partial') return 75;
        if ($this->status === 'confirmed') return 50;
        if ($this->status === 'sent') return 25;
        return 0;
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return $this->total_amount - $this->total_payments;
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->total_payments >= $this->total_amount) {
            return 'paid';
        } elseif ($this->total_payments > 0) {
            return 'partial';
        }
        return 'unpaid';
    }

    public function getPaymentStatusColorAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'green',
            'partial' => 'yellow',
            'unpaid' => 'red',
            default => 'gray'
        };
    }
}
