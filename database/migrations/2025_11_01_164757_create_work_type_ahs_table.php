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
        Schema::create('work_type_ahs', function (Blueprint $table) {
            $table->foreignId('work_type_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('unit_rate_analysis_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            // Set the primary key
            $table->primary(['work_type_id', 'unit_rate_analysis_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_type_ahs');
    }
};
