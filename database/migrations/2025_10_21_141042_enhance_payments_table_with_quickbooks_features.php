<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add payment category relationship
            $table->foreignId('payment_category_id')->nullable()->after('purchase_order_id')->constrained()->onDelete('set null');
            
            // Add bank reconciliation fields
            $table->date('cleared_date')->nullable()->after('received_by');
            $table->boolean('reconciled')->default(false)->after('cleared_date');
            $table->date('reconciled_date')->nullable()->after('reconciled');
            $table->string('reconciled_by')->nullable()->after('reconciled_date');
            
            // Add approval workflow
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('reconciled_by');
            $table->string('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Add accounting fields
            $table->foreignId('expense_account_id')->nullable()->after('approved_at')->constrained('accounts')->onDelete('set null');
            $table->foreignId('bank_account_id')->nullable()->after('expense_account_id')->constrained('accounts')->onDelete('set null');
            
            // Add void status
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'voided'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['payment_category_id']);
            $table->dropForeign(['expense_account_id']);
            $table->dropForeign(['bank_account_id']);
            
            $table->dropColumn([
                'payment_category_id',
                'cleared_date',
                'reconciled',
                'reconciled_date',
                'reconciled_by',
                'approval_status',
                'approved_by',
                'approved_at',
                'expense_account_id',
                'bank_account_id',
            ]);
            
            // Revert status enum
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->change();
        });
    }
};