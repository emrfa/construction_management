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
        // We are creating the table from scratch
        Schema::create('work_item_ahs', function (Blueprint $table) {
            
            // This is the link to the 'work_items' table
            $table->foreignId('work_item_id')
                  ->constrained()
                  ->cascadeOnDelete(); // If a work item is deleted, remove its recipes

            // This is the link to the 'unit_rate_analyses' table (your AHS table)
            $table->foreignId('unit_rate_analysis_id')
                  ->constrained()
                  ->cascadeOnDelete(); // If an AHS is deleted, remove it from recipes

            // This ensures you can't add the same AHS to the same Work Item twice
            $table->primary(['work_item_id', 'unit_rate_analysis_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_item_ahs');
    }
};