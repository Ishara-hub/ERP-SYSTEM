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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('accounts')->onDelete('cascade');
            $table->date('transaction_date');
            $table->enum('type', ['deposit', 'withdrawal', 'fee', 'interest', 'other']);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('check_number')->nullable();
            $table->enum('status', ['pending', 'cleared', 'reconciled', 'void'])->default('pending');
            
            // Reconciliation fields
            $table->boolean('reconciled')->default(false);
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reconciled_at')->nullable();
            
            // Matching fields for auto-reconciliation
            $table->decimal('matched_amount', 15, 2)->nullable();
            $table->string('match_confidence')->nullable(); // 'exact', 'high', 'medium', 'low'
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('bank_account_id');
            $table->index('transaction_date');
            $table->index('status');
            $table->index('reconciled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
