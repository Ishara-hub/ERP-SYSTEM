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
        // Handle potential schema drift across environments
        if (Schema::hasTable('stock_movements')) {
            // Rename product_id to item_id only if product_id exists and item_id does not
            if (Schema::hasColumn('stock_movements', 'product_id') && !Schema::hasColumn('stock_movements', 'item_id')) {
                Schema::table('stock_movements', function (Blueprint $table) {
                    $table->renameColumn('product_id', 'item_id');
                });
            }

            // Add missing columns idempotently
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movements', 'source_document')) {
                    $table->string('source_document')->nullable()->after('type');
                }
                if (!Schema::hasColumn('stock_movements', 'source_document_id')) {
                    $table->unsignedBigInteger('source_document_id')->nullable()->after('source_document');
                }
                if (!Schema::hasColumn('stock_movements', 'transaction_date')) {
                    $table->date('transaction_date')->nullable()->after('source_document_id');
                }
                if (!Schema::hasColumn('stock_movements', 'description')) {
                    $table->text('description')->nullable()->after('transaction_date');
                }
            });

            // Adjust quantity type if column exists
            if (Schema::hasColumn('stock_movements', 'quantity')) {
                try {
                    Schema::table('stock_movements', function (Blueprint $table) {
                        $table->decimal('quantity', 10, 2)->change();
                    });
                } catch (\Throwable $e) {
                    // Ignore if platform does not support change or already set
                }
            }

            // Adjust enum values for type if column exists
            if (Schema::hasColumn('stock_movements', 'type')) {
                try {
                    Schema::table('stock_movements', function (Blueprint $table) {
                        $table->enum('type', ['in', 'out', 'sale', 'purchase', 'adjustment'])->change();
                    });
                } catch (\Throwable $e) {
                    // Ignore if enum already compatible or platform limitation
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_movements')) {
            // Only rename back if item_id exists and product_id does not
            if (Schema::hasColumn('stock_movements', 'item_id') && !Schema::hasColumn('stock_movements', 'product_id')) {
                try {
                    Schema::table('stock_movements', function (Blueprint $table) {
                        $table->renameColumn('item_id', 'product_id');
                    });
                } catch (\Throwable $e) {
                    // Ignore rename issues during rollback
                }
            }

            // Drop added columns if they exist
            Schema::table('stock_movements', function (Blueprint $table) {
                $dropColumns = [];
                foreach (['source_document', 'source_document_id', 'transaction_date', 'description'] as $col) {
                    if (Schema::hasColumn('stock_movements', $col)) {
                        $dropColumns[] = $col;
                    }
                }
                if (!empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });

            // Revert quantity type if possible
            if (Schema::hasColumn('stock_movements', 'quantity')) {
                try {
                    Schema::table('stock_movements', function (Blueprint $table) {
                        $table->integer('quantity')->change();
                    });
                } catch (\Throwable $e) {
                    // Ignore platform limitations
                }
            }

            // Revert enum if possible
            if (Schema::hasColumn('stock_movements', 'type')) {
                try {
                    Schema::table('stock_movements', function (Blueprint $table) {
                        $table->enum('type', ['in', 'out'])->change();
                    });
                } catch (\Throwable $e) {
                    // Ignore platform limitations
                }
            }
        }
    }
};