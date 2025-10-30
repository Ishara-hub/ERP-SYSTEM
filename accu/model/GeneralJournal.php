<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralJournal extends Model
{
    use HasFactory;
    protected $table = 'general_journal';
    protected $fillable = [
        'transaction_date', 'reference', 'description', 'created_by', 'branch_id'
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'journal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }
}
