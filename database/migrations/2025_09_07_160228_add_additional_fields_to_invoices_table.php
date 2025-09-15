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
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('po_number')->nullable();
            $table->string('terms')->nullable();
            $table->string('rep')->nullable();
            $table->date('ship_date')->nullable();
            $table->string('via')->nullable();
            $table->string('fob')->nullable();
            $table->string('customer_message')->nullable();
            $table->text('memo')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('payments_applied', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->string('template')->default('default');
            $table->boolean('is_online_payment_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'billing_address',
                'shipping_address',
                'po_number',
                'terms',
                'rep',
                'ship_date',
                'via',
                'fob',
                'customer_message',
                'memo',
                'subtotal',
                'tax_amount',
                'discount_amount',
                'shipping_amount',
                'payments_applied',
                'balance_due',
                'template',
                'is_online_payment_enabled'
            ]);
        });
    }
};