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
        Schema::create('unit_rate_equipments', function (Blueprint $table) {
           $table->id();
            // Link to the AHS Header
            $table->foreignId('unit_rate_analysis_id')->constrained()->onDelete('cascade');
            // Link to the Equipment from Equipment Master
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            
            // Coefficient / Quantity (e.g., 0.1 Hours, 0.5 Days)
            $table->decimal('coefficient', 15, 4);
            
            // The cost for that coefficient (e.g., rental rate per hour/day)
            $table->decimal('cost_rate', 15, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_rate_equipments');
    }
};
