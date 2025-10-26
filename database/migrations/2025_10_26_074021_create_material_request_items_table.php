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
        Schema::create('material_request_items', function (Blueprint $table) {
           $table->id();
            $table->foreignId('material_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade'); // The material needed
            // Optional: Link back to the specific WBS/Quotation Item this is for
            $table->foreignId('quotation_item_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity_requested', 15, 2);
            $table->decimal('quantity_fulfilled', 15, 2)->default(0); // How much has been issued/ordered
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_request_items');
    }
};
