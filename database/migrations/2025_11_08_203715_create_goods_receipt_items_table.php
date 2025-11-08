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
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
    $table->foreignId('goods_receipt_id')->constrained()->onDelete('cascade');
    $table->foreignId('inventory_item_id')->constrained()->onDelete('restrict');

    // This is the link back to the PO line
    $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();

    $table->decimal('quantity_received', 15, 2);
    $table->decimal('unit_cost', 15, 2)->default(0);
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
