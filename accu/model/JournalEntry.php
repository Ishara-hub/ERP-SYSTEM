<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;
    protected $table = 'journal_entries';
    protected $fillable = [
        'journal_id', 'account_id', 'sub_account_id', 'debit', 'credit', 'description', 'branch_id'
    ];

    public function journal()
    {
        return $this->belongsTo(GeneralJournal::class, 'journal_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function subAccount()
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }
}
