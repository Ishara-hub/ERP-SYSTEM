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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Rename product_id to item_id
            $table->renameColumn('product_id', 'item_id');
            
            // Add new columns
            $table->string('source_document')->nullable()->after('type');
            $table->unsignedBigInteger('source_document_id')->nullable()->after('source_document');
            $table->date('transaction_date')->nullable()->after('source_document_id');
            $table->text('description')->nullable()->after('transaction_date');
            
            // Update quantity to allow decimals
            $table->decimal('quantity', 10, 2)->change();
            
            // Update type enum to include 'sale'
            $table->enum('type', ['in', 'out', 'sale', 'purchase', 'adjustment'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('item_id', 'product_id');
            $table->dropColumn(['source_document', 'source_document_id', 'transaction_date', 'description']);
            $table->integer('quantity')->change();
            $table->enum('type', ['in', 'out'])->change();
        });
    }
};