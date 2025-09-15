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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('contact_person')->nullable()->after('company_name');
            $table->string('website')->nullable()->after('email');
            $table->string('tax_id')->nullable()->after('website');
            $table->string('payment_terms')->nullable()->after('tax_id');
            $table->decimal('credit_limit', 15, 2)->nullable()->after('payment_terms');
            $table->string('currency', 3)->default('USD')->after('credit_limit');
            $table->text('notes')->nullable()->after('currency');
            $table->boolean('is_active')->default(true)->after('notes');
            $table->string('supplier_code')->unique()->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'contact_person',
                'website',
                'tax_id',
                'payment_terms',
                'credit_limit',
                'currency',
                'notes',
                'is_active',
                'supplier_code'
            ]);
        });
    }
};