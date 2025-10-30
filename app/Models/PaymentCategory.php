<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'color',
        'is_active',
        'requires_reference',
        'requires_approval',
        'allowed_payment_methods',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_reference' => 'boolean',
        'requires_approval' => 'boolean',
        'allowed_payment_methods' => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public static function getDefaultCategories()
    {
        return [
            [
                'name' => 'Vendor Payments',
                'code' => 'VENDOR',
                'description' => 'Payments to suppliers and vendors for purchase orders',
                'icon' => 'truck',
                'color' => 'blue',
                'requires_reference' => true,
                'requires_approval' => false,
                'allowed_payment_methods' => ['cash', 'check', 'bank_transfer', 'credit_card'],
            ],
            [
                'name' => 'Customer Payments',
                'code' => 'CUSTOMER',
                'description' => 'Payments received from customers for invoices',
                'icon' => 'user',
                'color' => 'green',
                'requires_reference' => true,
                'requires_approval' => false,
                'allowed_payment_methods' => ['cash', 'check', 'bank_transfer', 'credit_card'],
            ],
            [
                'name' => 'Expense Payments',
                'code' => 'EXPENSE',
                'description' => 'General business expense payments',
                'icon' => 'receipt',
                'color' => 'orange',
                'requires_reference' => true,
                'requires_approval' => true,
                'allowed_payment_methods' => ['cash', 'check', 'bank_transfer', 'credit_card'],
            ],
            [
                'name' => 'Payroll',
                'code' => 'PAYROLL',
                'description' => 'Employee salary and wage payments',
                'icon' => 'users',
                'color' => 'purple',
                'requires_reference' => false,
                'requires_approval' => true,
                'allowed_payment_methods' => ['bank_transfer', 'check'],
            ],
            [
                'name' => 'Tax Payments',
                'code' => 'TAX',
                'description' => 'Government tax payments',
                'icon' => 'building',
                'color' => 'red',
                'requires_reference' => true,
                'requires_approval' => true,
                'allowed_payment_methods' => ['bank_transfer', 'check'],
            ],
            [
                'name' => 'Transfer Payments',
                'code' => 'TRANSFER',
                'description' => 'Transfers between bank accounts',
                'icon' => 'arrow-right-left',
                'color' => 'gray',
                'requires_reference' => true,
                'requires_approval' => false,
                'allowed_payment_methods' => ['bank_transfer'],
            ],
        ];
    }
}