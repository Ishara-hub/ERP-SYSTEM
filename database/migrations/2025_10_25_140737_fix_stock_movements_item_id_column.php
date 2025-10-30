<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if item_id column doesn't exist
        if (!Schema::hasColumn('stock_movements', 'item_id')) {
            // Add item_id column
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('item_id')->nullable()->after('id');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            });
            
            // Copy data from product_id to item_id (if product_id exists)
            if (Schema::hasColumn('stock_movements', 'product_id')) {
                DB::statement('UPDATE stock_movements SET item_id = product_id WHERE product_id IS NOT NULL');
            }
            
            // Make item_id non-nullable after copying data
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('item_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove item_id column if it exists
        if (Schema::hasColumn('stock_movements', 'item_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->dropColumn('item_id');
            });
        }
    }
};
