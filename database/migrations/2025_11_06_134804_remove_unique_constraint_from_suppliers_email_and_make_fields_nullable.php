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
            // Drop the unique constraint on email
            $table->dropUnique(['email']);
            // Make email nullable to allow empty emails
            $table->string('email')->nullable()->change();
            // Make phone and address nullable to allow empty values
            $table->string('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Make email not nullable again
            $table->string('email')->nullable(false)->change();
            // Restore the unique constraint
            $table->unique('email');
            // Make phone and address not nullable again
            $table->string('phone')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }
};
