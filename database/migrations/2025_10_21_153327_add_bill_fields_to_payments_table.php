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
            $table->foreignId('bill_id')->nullable()->after('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->after('bill_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bill_id']);
            $table->dropColumn('bill_id');
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};