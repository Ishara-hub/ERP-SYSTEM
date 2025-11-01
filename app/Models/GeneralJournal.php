<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneralJournal extends Model
{
    protected $fillable = [
        'transaction_date',
        'reference',
        'description',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            if (empty($journal->reference)) {
                $journal->reference = self::generateReference();
            }
        });
    }

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateReference()
    {
        $lastJournal = self::latest('id')->first();
        $nextNumber = $lastJournal ? $lastJournal->id + 1 : 1;
        return 'JE-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
