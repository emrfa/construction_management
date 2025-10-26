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
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreignId('unit_rate_analysis_id')
              ->nullable() // Allow manual items not linked to AHS
              ->constrained() // Links to unit_rate_analyses table
              ->nullOnDelete() // If AHS is deleted, keep the quote item but remove link
              ->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
        $table->dropForeign(['unit_rate_analysis_id']);
        $table->dropColumn('unit_rate_analysis_id');
        });
    }
};
