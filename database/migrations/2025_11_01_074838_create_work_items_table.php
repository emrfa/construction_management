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
        Schema::create('work_items', function (Blueprint $table) {
           $table->id();

            // Link to the parent category (e.g., "Struktur")
            $table->foreignId('work_type_id')
                  ->constrained('work_types')
                  ->onDelete('cascade');

            // Link to the default AHS for this task
            $table->foreignId('unit_rate_analysis_id')
                  ->nullable() 
                  ->constrained('unit_rate_analyses')
                  ->nullOnDelete(); 

            $table->string('name'); 
            $table->string('uom')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_items');
    }
};
