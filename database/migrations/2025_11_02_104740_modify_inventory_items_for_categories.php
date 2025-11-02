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
    Schema::table('inventory_items', function (Blueprint $table) {
            // 1. Drop the old text column
            // We check if the column exists before dropping, just in case.
            if (Schema::hasColumn('inventory_items', 'category')) {
                $table->dropColumn('category');
            }

            // 2. Add the new foreign key
            $table->foreignId('category_id')
                  ->nullable()
                  ->after('item_code')
                  ->constrained('item_categories') // Links to the new table
                  ->nullOnDelete();

            // 3. Add the new price column
            $table->decimal('base_purchase_price', 15, 2)
                  ->default(0)
                  ->after('uom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->dropColumn('base_purchase_price');
            
            // Add the old column back
            $table->string('category')->nullable()->after('item_code');
        });
    }
};
