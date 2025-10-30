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
        // Drop the incorrect foreign key constraint if it exists
        try {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist, ignore the error
        }
        
        // Check if the constraint name is different and try to drop it
        try {
            DB::statement('ALTER TABLE stock_movements DROP FOREIGN KEY stock_movements_product_id_foreign');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
        
        try {
            DB::statement('ALTER TABLE stock_movements DROP FOREIGN KEY stock_movements_item_id_foreign');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
        
        // Add the correct foreign key that references items table
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
        });
    }
};
