<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_account_id', 'sub_account_code', 'sub_account_name', 'description', 'is_active'
    ];

    public function parentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }
}
