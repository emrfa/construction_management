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
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_location_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('average_unit_cost', 15, 4)->default(0);
            // Optional: Track which transaction last updated this balance for audit
            $table->unsignedBigInteger('last_transaction_id')->nullable();
            $table->timestamps();

            $table->unique(['inventory_item_id', 'stock_location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
