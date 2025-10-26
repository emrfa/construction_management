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

        // Links to the Item Master
        $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');

        // The + or - quantity
        // e.g., +100 for Stock In, -25 for Stock Out
        $table->decimal('quantity', 15, 2); 

        // The cost of this transaction
        $table->decimal('unit_cost', 15, 2)->default(0); 

        // Links to the source (e.g., a PO or a Project)
        // We use a "polymorphic" relationship for this
        $table->morphs('sourceable'); // Creates `sourceable_id` and `sourceable_type`

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
