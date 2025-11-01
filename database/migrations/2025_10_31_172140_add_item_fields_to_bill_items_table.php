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
        Schema::table('bill_items', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->after('bill_id')->constrained()->onDelete('set null');
            $table->decimal('quantity', 15, 2)->nullable()->after('description');
            $table->decimal('unit_price', 15, 2)->nullable()->after('quantity');
            $table->foreignId('purchase_order_item_id')->nullable()->after('item_id')->constrained('purchase_order_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn(['item_id', 'quantity', 'unit_price', 'purchase_order_item_id']);
        });
    }
};
