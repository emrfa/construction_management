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
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
        $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->onDelete('cascade');
        $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('restrict');
        $table->decimal('system_qty', 15, 2);
        $table->decimal('physical_qty', 15, 2);
        $table->decimal('adjustment_qty', 15, 2);
        $table->decimal('unit_cost', 15, 2)->default(0);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
