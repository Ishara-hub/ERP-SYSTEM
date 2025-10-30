<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'invoice_no',
        'date',
        'due_date',
        'total_amount',
        'status',
        'billing_address',
        'shipping_address',
        'po_number',
        'terms',
        'rep',
        'ship_date',
        'via',
        'fob',
        'customer_message',
        'memo',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'payments_applied',
        'balance_due',
        'template',
        'is_online_payment_enabled',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'ship_date' => 'date',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'payments_applied' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'is_online_payment_enabled' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_no)) {
                $invoice->invoice_no = self::generateInvoiceNumber();
            }
        });

        static::saving(function ($invoice) {
            // Calculate balance due
            $invoice->balance_due = $invoice->total_amount - $invoice->payments_applied;
        });
    }

    public static function generateInvoiceNumber()
    {
        $lastInvoice = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $subtotal = $this->lineItems->sum('amount');
        $taxAmount = $this->lineItems->sum('tax_amount');
        $total = $subtotal + $taxAmount + $this->shipping_amount - $this->discount_amount;
        
        // Calculate total payments applied
        $paymentsApplied = $this->payments()->where('status', 'completed')->sum('amount');
        
        // Determine status based on payments
        $status = 'unpaid';
        if ($paymentsApplied >= $total) {
            $status = 'paid';
        } elseif ($paymentsApplied > 0) {
            $status = 'partial';
        }

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
            'payments_applied' => $paymentsApplied,
            'balance_due' => $total - $paymentsApplied,
            'status' => $status,
        ]);
    }
}
