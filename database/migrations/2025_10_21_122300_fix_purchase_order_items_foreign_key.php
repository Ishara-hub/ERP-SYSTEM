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
            // Drop the existing foreign key constraint
            $table->dropForeign('purchase_order_items_product_id_foreign');
            
            // Add new foreign key constraint pointing to items table
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Drop the items foreign key
            $table->dropForeign(['item_id']);
            
            // Restore the original products foreign key
            $table->foreign('item_id', 'purchase_order_items_product_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }
};