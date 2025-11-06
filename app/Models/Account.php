<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'parent_id',
        'opening_balance',
        'current_balance',
        'description',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Account Types
    const ASSET = 'Asset';
    const LIABILITY = 'Liability';
    const EQUITY = 'Equity';
    const INCOME = 'Income';
    const EXPENSE = 'Expense';
    
    // New Account Types
    const ACCOUNTS_RECEIVABLE = 'Accounts Receivable';
    const OTHER_CURRENT_ASSET = 'Other Current Asset';
    const FIXED_ASSET = 'Fixed Asset';
    const ACCOUNTS_PAYABLE = 'Accounts Payable';
    const OTHER_CURRENT_LIABILITY = 'Other Current Liability';
    const COST_OF_GOODS_SOLD = 'Cost of Goods Sold';
    const BANK = 'Bank';

    // Account Subtypes for Assets (deprecated - use new types instead)
    const CURRENT_ASSET = 'Current Asset';
    const OTHER_ASSET = 'Other Asset';
    
    // Note: FIXED_ASSET is already defined above in New Account Types

    // Account Subtypes for Liabilities
    const CURRENT_LIABILITY = 'Current Liability';
    const LONG_TERM_LIABILITY = 'Long Term Liability';

    // Account Subtypes for Equity
    const OWNERS_EQUITY = 'Owners Equity';
    const RETAINED_EARNINGS = 'Retained Earnings';

    // Account Subtypes for Income
    const OPERATING_INCOME = 'Operating Income';
    const OTHER_INCOME = 'Other Income';

    // Account Subtypes for Expenses
    const OPERATING_EXPENSE = 'Operating Expense';
    const OTHER_EXPENSE = 'Other Expense';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('sort_order');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function debitJournals(): HasMany
    {
        return $this->hasMany(Journal::class, 'debit_account_id');
    }

    public function creditJournals(): HasMany
    {
        return $this->hasMany(Journal::class, 'credit_account_id');
    }

    // Scope for active accounts
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for system accounts
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    // Scope for non-system accounts
    public function scopeUserCreated($query)
    {
        return $query->where('is_system', false);
    }

    // Get account type color for UI
    public function getAccountTypeColorAttribute(): string
    {
        return match($this->account_type) {
            self::ASSET, self::ACCOUNTS_RECEIVABLE, self::OTHER_CURRENT_ASSET, 
            self::FIXED_ASSET, self::BANK => 'text-green-600',
            self::LIABILITY, self::ACCOUNTS_PAYABLE, self::OTHER_CURRENT_LIABILITY => 'text-red-600',
            self::EQUITY => 'text-blue-600',
            self::INCOME => 'text-purple-600',
            self::EXPENSE, self::COST_OF_GOODS_SOLD => 'text-orange-600',
            default => 'text-gray-600'
        };
    }

    // Get account type background color for UI
    public function getAccountTypeBgColorAttribute(): string
    {
        return match($this->account_type) {
            self::ASSET, self::ACCOUNTS_RECEIVABLE, self::OTHER_CURRENT_ASSET, 
            self::FIXED_ASSET, self::BANK => 'bg-green-100',
            self::LIABILITY, self::ACCOUNTS_PAYABLE, self::OTHER_CURRENT_LIABILITY => 'bg-red-100',
            self::EQUITY => 'bg-blue-100',
            self::INCOME => 'bg-purple-100',
            self::EXPENSE, self::COST_OF_GOODS_SOLD => 'bg-orange-100',
            default => 'bg-gray-100'
        };
    }

    // Check if account has children
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    // Get full account path (e.g., "Assets > Current Assets > Cash")
    public function getFullPathAttribute(): string
    {
        $path = [$this->account_name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->account_name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get all journal entries for this account (both debit and credit)
     */
    public function journalEntries()
    {
        $debitEntries = $this->debitJournals()->with('creditAccount', 'transaction')->get();
        $creditEntries = $this->creditJournals()->with('debitAccount', 'transaction')->get();
        
        return $debitEntries->concat($creditEntries)->sortBy('date');
    }
}
