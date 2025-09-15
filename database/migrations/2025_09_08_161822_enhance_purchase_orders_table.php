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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Rename existing columns
            $table->renameColumn('po_no', 'po_number');
            $table->renameColumn('date', 'order_date');
            
            // Add new columns
            $table->date('expected_delivery_date')->nullable()->after('order_date');
            $table->date('actual_delivery_date')->nullable()->after('expected_delivery_date');
            $table->decimal('subtotal', 15, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('shipping_amount', 15, 2)->default(0)->after('tax_amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('shipping_amount');
            $table->text('shipping_address')->nullable()->after('status');
            $table->text('billing_address')->nullable()->after('shipping_address');
            $table->string('terms')->nullable()->after('billing_address');
            $table->string('reference')->nullable()->after('terms');
            $table->text('notes')->nullable()->after('reference');
            $table->string('created_by')->nullable()->after('notes');
            $table->string('approved_by')->nullable()->after('created_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Update status enum
            $table->dropColumn('status');
        });
        
        // Add the new status column with updated enum values
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status', ['draft', 'sent', 'confirmed', 'partial', 'received', 'cancelled'])->default('draft')->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->renameColumn('po_number', 'po_no');
            $table->renameColumn('order_date', 'date');
            $table->dropColumn([
                'expected_delivery_date',
                'actual_delivery_date',
                'subtotal',
                'tax_amount',
                'shipping_amount',
                'discount_amount',
                'shipping_address',
                'billing_address',
                'terms',
                'reference',
                'notes',
                'created_by',
                'approved_by',
                'approved_at'
            ]);
        });
        
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'approved', 'received', 'cancelled'])->default('pending');
        });
    }
};