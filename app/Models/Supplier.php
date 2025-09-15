<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'website',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'currency',
        'notes',
        'is_active',
        'supplier_code',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->supplier_code)) {
                $supplier->supplier_code = self::generateSupplierCode();
            }
        });
    }

    public static function generateSupplierCode()
    {
        $lastSupplier = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastSupplier ? $lastSupplier->id + 1 : 1;
        return 'SUP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getDisplayNameAttribute()
    {
        return $this->company_name ?: $this->name;
    }

    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'green' : 'red';
    }
}
