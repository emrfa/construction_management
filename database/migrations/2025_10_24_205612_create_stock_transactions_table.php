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
        Schema::create('stock_transactions', function (Blueprint $table) {
        $table->id();

            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            
            // This is the new location link
            $table->foreignId('stock_location_id')->constrained('stock_locations')->onDelete('restrict');

            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);

            $table->morphs('sourceable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
