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
        Schema::create('transfer_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('internal_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('source_location_id')->constrained('stock_locations');
            $table->foreignId('destination_location_id')->constrained('stock_locations');
            $table->date('shipped_date');
            $table->enum('status', ['in_transit', 'received'])->default('in_transit');
            $table->foreignId('shipped_by_user_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('transfer_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained();
            $table->decimal('quantity_shipped', 15, 4);
            $table->decimal('quantity_received', 15, 4)->default(0);
            // We store the unit cost at the time of shipment (WAC from source)
            $table->decimal('unit_cost', 15, 4); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_shipments');
    }
};
