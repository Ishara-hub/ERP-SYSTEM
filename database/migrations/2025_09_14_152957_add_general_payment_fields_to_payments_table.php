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
            $table->string('payment_type')->nullable()->after('status');
            $table->string('payee')->nullable()->after('payment_type');
            $table->text('address')->nullable()->after('payee');
            $table->boolean('print_later')->default(false)->after('address');
            $table->boolean('pay_online')->default(false)->after('print_later');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'payee', 'address', 'print_later', 'pay_online']);
        });
    }
};
