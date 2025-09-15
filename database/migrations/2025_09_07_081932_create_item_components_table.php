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
        Schema::create('item_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('component_item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 4)->default(1);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure unique combination of assembly and component
            $table->unique(['assembly_item_id', 'component_item_id']);
            
            // Indexes
            $table->index('assembly_item_id');
            $table->index('component_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_components');
    }
};