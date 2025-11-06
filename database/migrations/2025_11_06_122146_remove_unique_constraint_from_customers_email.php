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
            // Drop the unique constraint on email
            $table->dropUnique(['email']);
            // Make email nullable to allow empty emails
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Make email not nullable again
            $table->string('email')->nullable(false)->change();
            // Restore the unique constraint
            $table->unique('email');
        });
    }
};
