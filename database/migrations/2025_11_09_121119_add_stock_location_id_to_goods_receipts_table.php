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
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('stock_location_id')
                  ->nullable() // Nullable just in case, but we should always set it
                  ->after('project_id') // Place it after the project_id
                  ->constrained('stock_locations')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['stock_location_id']);
            $table->dropColumn('stock_location_id');
        });
    }
};
