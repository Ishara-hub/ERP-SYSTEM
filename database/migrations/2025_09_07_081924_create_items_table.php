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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('item_number')->unique()->nullable();
            $table->enum('item_type', ['Service', 'Inventory Part', 'Inventory Assembly', 'Non-Inventory Part', 'Other Charge', 'Discount', 'Group', 'Payment']);
            $table->foreignId('parent_id')->nullable()->constrained('items')->onDelete('cascade');
            $table->string('manufacturer_part_number')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->boolean('enable_unit_of_measure')->default(false);
            
            // Purchase Information
            $table->text('purchase_description')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('cost_method')->default('global_preference'); // global_preference, manual
            $table->foreignId('cogs_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('preferred_vendor_id')->nullable()->constrained('suppliers')->onDelete('set null');
            
            // Sales Information
            $table->text('sales_description')->nullable();
            $table->decimal('sales_price', 15, 2)->default(0);
            $table->decimal('markup_percentage', 5, 2)->nullable();
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->foreignId('income_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            
            // Inventory Information (for inventory items)
            $table->foreignId('asset_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('reorder_point', 10, 2)->nullable();
            $table->decimal('max_quantity', 10, 2)->nullable();
            $table->decimal('on_hand', 10, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->date('as_of_date')->nullable();
            
            // Service specific fields
            $table->boolean('is_used_in_assemblies')->default(false);
            $table->boolean('is_performed_by_subcontractor')->default(false);
            
            // Assembly specific fields
            $table->boolean('purchase_from_vendor')->default(false);
            $table->decimal('build_point_min', 10, 2)->nullable();
            
            // Status and settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_inactive')->default(false);
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['item_type', 'is_active']);
            $table->index(['parent_id', 'is_active']);
            $table->index('item_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};