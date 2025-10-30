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
        Schema::create('payment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('blue');
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_reference')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->json('allowed_payment_methods')->nullable(); // ['cash', 'check', 'bank_transfer', 'credit_card']
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_categories');
    }
};