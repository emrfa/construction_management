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
        Schema::create('material_usages', function (Blueprint $table) {
          $table->id();
        // Link to the specific progress update log entry
        $table->foreignId('progress_update_id')->constrained()->onDelete('cascade');
        // Link to the material used from Item Master
        $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
        // How much was used
        $table->decimal('quantity_used', 15, 2);
        // Optional: Store the unit cost at the time of usage for reporting
        $table->decimal('unit_cost', 15, 2)->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_usages');
    }
};
