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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Rename product_id to item_id
            $table->renameColumn('product_id', 'item_id');
            
            // Add new columns
            $table->string('description')->after('item_id');
            $table->decimal('received_quantity', 10, 2)->default(0)->after('quantity');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            $table->string('unit_of_measure')->nullable()->after('tax_amount');
            $table->text('notes')->nullable()->after('unit_of_measure');
            
            // Update existing columns
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('unit_price', 15, 2)->change();
            $table->decimal('subtotal', 15, 2)->change();
            $table->renameColumn('subtotal', 'amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->renameColumn('item_id', 'product_id');
            $table->renameColumn('amount', 'subtotal');
            $table->dropColumn([
                'description',
                'received_quantity',
                'tax_rate',
                'tax_amount',
                'unit_of_measure',
                'notes'
            ]);
        });
    }
};