<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'type',
        'amount',
        'description',
        'reference_number',
        'check_number',
        'status',
        'reconciled',
        'payment_id',
        'reconciled_by',
        'reconciled_at',
        'matched_amount',
        'match_confidence',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'matched_amount' => 'decimal:2',
        'reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    // Transaction Types
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_FEE = 'fee';
    const TYPE_INTEREST = 'interest';
    const TYPE_OTHER = 'other';

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_CLEARED = 'cleared';
    const STATUS_RECONCILED = 'reconciled';
    const STATUS_VOID = 'void';

    // Match Confidence Constants
    const MATCH_EXACT = 'exact';
    const MATCH_HIGH = 'high';
    const MATCH_MEDIUM = 'medium';
    const MATCH_LOW = 'low';

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    // Scopes
    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false);
    }

    public function scopeReconciled($query)
    {
        return $query->where('reconciled', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('bank_account_id', $accountId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
