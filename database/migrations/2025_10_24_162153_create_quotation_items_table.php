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
        Schema::create('quotation_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
        
        // THIS IS THE MAGIC KEY
        // It links to another row in this *same table*
        $table->foreignId('parent_id')
              ->nullable()
              ->constrained('quotation_items') // Self-referencing
              ->onDelete('cascade');

        $table->string('description');
        $table->string('item_code')->nullable();     // For "B.13.d.2"
        $table->string('uom')->nullable();          // For "M3", "M2"
        $table->decimal('quantity', 15, 2)->nullable(); // Nullable for headers
        $table->decimal('unit_price', 15, 2)->nullable(); // Nullable for headers
        $table->decimal('subtotal', 15, 2)->nullable(); // `qty * price` or `sum(children)`
        $table->integer('sort_order')->default(0);  // To keep them in order
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
