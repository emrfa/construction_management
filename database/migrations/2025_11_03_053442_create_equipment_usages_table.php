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
        Schema::create('equipment_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_update_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            
            // The quantity and unit the user *entered*
            $table->decimal('quantity_used', 15, 2);
            $table->string('unit_used', 50); // e.g., "Hour", "Day"

            // The final calculated cost for this usage
            $table->decimal('total_cost', 15, 2); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_usages');
    }
};