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

    protected $attributes = [
        'total_amount' => 0,
        'subtotal' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'shipping_amount' => 0,
        'payments_applied' => 0,
        'balance_due' => 0,
        'status' => 'unpaid',
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
        // Find the maximum numeric part among all invoices starting with 'INV-'
        $maxInv = self::where('invoice_no', 'like', 'INV-%')
            ->selectRaw("MAX(CAST(SUBSTRING(invoice_no, 5) AS UNSIGNED)) as max_num")
            ->first();
            
        $nextNumber = 1;
        if ($maxInv && $maxInv->max_num) {
            $nextNumber = $maxInv->max_num + 1;
        } else {
            // Fallback for purely numeric invoice numbers if no INV- prefix exists
            $maxNumeric = self::whereRaw('invoice_no REGEXP "^[0-9]+$"')
                ->selectRaw("MAX(CAST(invoice_no AS UNSIGNED)) as max_num")
                ->first();
            if ($maxNumeric && $maxNumeric->max_num) {
                $nextNumber = $maxNumeric->max_num + 1;
            }
        }
        
        return 'INV-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
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
