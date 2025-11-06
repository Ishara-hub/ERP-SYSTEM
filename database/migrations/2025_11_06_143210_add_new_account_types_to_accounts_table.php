<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include new account types
        DB::statement("ALTER TABLE accounts MODIFY COLUMN account_type ENUM(
            'Asset',
            'Liability',
            'Income',
            'Expense',
            'Equity',
            'Accounts Receivable',
            'Other Current Asset',
            'Fixed Asset',
            'Accounts Payable',
            'Other Current Liability',
            'Cost of Goods Sold',
            'Bank'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE accounts MODIFY COLUMN account_type ENUM(
            'Asset',
            'Liability',
            'Income',
            'Expense',
            'Equity'
        )");
    }
};
