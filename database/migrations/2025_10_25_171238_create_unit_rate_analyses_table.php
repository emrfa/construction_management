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
        Schema::create('unit_rate_analyses', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // Unique code for the AHS (e.g., 'AHS.CONC.K225')
        $table->string('name'); // e.g., 'Analisa Beton K-225 per m³'
        $table->string('unit'); // The unit the analysis calculates for (e.g., 'm³', 'm²', 'bh')
        $table->decimal('total_cost', 15, 2)->default(0); // Auto-calculated later
        $table->text('notes')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_rate_analyses');
    }
};
