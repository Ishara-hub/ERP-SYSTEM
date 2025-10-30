<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id', 'account_code', 'account_name', 'description', 'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }

    public function subAccounts()
    {
        return $this->hasMany(SubAccount::class, 'parent_account_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }
}
