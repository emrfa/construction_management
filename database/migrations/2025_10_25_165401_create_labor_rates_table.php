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
        Schema::create('labor_rates', function (Blueprint $table) {
            $table->id();
            $table->string('labor_type')->unique(); // e.g., 'Tukang Batu', 'Mandor', 'Helper'
            $table->string('unit'); // e.g., 'Hari', 'Jam' (OH - Orang Hari, OJ - Orang Jam)
            $table->decimal('rate', 15, 2); // Cost per unit (Rp)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_rates');
    }
};
