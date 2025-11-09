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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
        $table->string('adjustment_no')->unique();
        $table->foreignId('stock_location_id')->constrained('stock_locations')->onDelete('restrict');
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        $table->date('adjustment_date');
        $table->text('reason');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
