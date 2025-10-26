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
        Schema::create('unit_rate_materials', function (Blueprint $table) {
        $table->id();
        // Link to the AHS Header
        $table->foreignId('unit_rate_analysis_id')->constrained()->onDelete('cascade');
        // Link to the Material from Item Master
        $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
        // Coefficient / Quantity needed per AHS unit
        $table->decimal('coefficient', 15, 4); // Use enough precision
        // Optional: Store the unit cost *at the time of AHS creation*
        $table->decimal('unit_cost', 15, 2)->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_rate_materials');
    }
};
