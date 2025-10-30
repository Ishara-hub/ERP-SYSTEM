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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('company')->nullable()->after('address');
            $table->string('contact_person')->nullable()->after('company');
            $table->text('notes')->nullable()->after('contact_person');
            $table->boolean('is_active')->default(true)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['company', 'contact_person', 'notes', 'is_active']);
        });
    }
};
