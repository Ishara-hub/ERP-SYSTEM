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
            // Make invoice_id nullable to support purchase order payments
            $table->foreignId('invoice_id')->nullable()->change();
            
            // Add purchase order relationship
            $table->foreignId('purchase_order_id')->nullable()->after('invoice_id')->constrained()->onDelete('cascade');
            
            // Add payment type to distinguish between invoice and purchase order payments
            $table->enum('payment_category', ['invoice', 'purchase_order', 'general'])->default('invoice')->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn(['purchase_order_id', 'payment_category']);
            
            // Revert invoice_id to not nullable
            $table->foreignId('invoice_id')->nullable(false)->change();
        });
    }
};