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
            $table->string('payment_number')->unique()->after('id');
            $table->text('notes')->nullable()->after('reference');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed')->after('notes');
            $table->string('bank_name')->nullable()->after('status');
            $table->string('check_number')->nullable()->after('bank_name');
            $table->string('transaction_id')->nullable()->after('check_number');
            $table->decimal('fee_amount', 15, 2)->default(0)->after('transaction_id');
            $table->string('received_by')->nullable()->after('fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_number',
                'notes',
                'status',
                'bank_name',
                'check_number',
                'transaction_id',
                'fee_amount',
                'received_by'
            ]);
        });
    }
};