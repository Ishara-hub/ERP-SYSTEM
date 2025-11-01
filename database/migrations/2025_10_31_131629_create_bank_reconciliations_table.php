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
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('accounts')->onDelete('cascade');
            $table->date('statement_date');
            $table->decimal('beginning_balance', 15, 2);
            $table->decimal('ending_balance', 15, 2);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->date('service_charge_date')->nullable();
            $table->foreignId('service_charge_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('interest_earned', 15, 2)->default(0);
            $table->date('interest_earned_date')->nullable();
            $table->foreignId('interest_earned_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('cleared_balance', 15, 2);
            $table->decimal('difference', 15, 2);
            $table->boolean('is_completed')->default(false);
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};
