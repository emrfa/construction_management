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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('material_request_item_id')
              ->nullable() // PO items might not originate from a request
              ->constrained() // Link to material_request_items table
              ->nullOnDelete() // If request item deleted, keep PO item but remove link
              ->after('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['material_request_item_id']);
        $table->dropColumn('material_request_item_id');
        });
    }
};
