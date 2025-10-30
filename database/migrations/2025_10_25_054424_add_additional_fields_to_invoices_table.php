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
            // Only add columns that don't exist
            if (!Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('date');
            }
            if (!Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('invoices', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_amount');
            }
            if (!Schema::hasColumn('invoices', 'shipping_amount')) {
                $table->decimal('shipping_amount', 15, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('invoices', 'payments_applied')) {
                $table->decimal('payments_applied', 15, 2)->default(0)->after('shipping_amount');
            }
            if (!Schema::hasColumn('invoices', 'balance_due')) {
                $table->decimal('balance_due', 15, 2)->default(0)->after('payments_applied');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'due_date',
                'subtotal',
                'tax_amount',
                'discount_amount',
                'shipping_amount',
                'payments_applied',
                'balance_due'
            ]);
        });
    }
};