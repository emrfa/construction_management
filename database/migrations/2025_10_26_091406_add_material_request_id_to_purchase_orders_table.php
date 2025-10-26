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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('material_request_id')
              ->nullable() // PO might not originate from a request
              ->constrained() // Link to material_requests table
              ->nullOnDelete() // If request deleted, keep PO but remove link
              ->after('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['material_request_id']);
        $table->dropColumn('material_request_id');
        });
    }
};
