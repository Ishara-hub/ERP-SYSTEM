<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];

    public function accounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }
}
