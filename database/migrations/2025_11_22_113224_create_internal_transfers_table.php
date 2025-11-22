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
        Schema::create('internal_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('source_location_id')->constrained('stock_locations');
            $table->foreignId('destination_location_id')->constrained('stock_locations');
            $table->enum('status', ['draft', 'approved', 'processing', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by_user_id')->constrained('users');
            // Optional: Link to Material Request if generated from one
            // We use unsignedBigInteger and nullable because material_requests might be in a different migration order or we want loose coupling
            $table->unsignedBigInteger('material_request_id')->nullable(); 
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('internal_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained();
            $table->decimal('quantity_requested', 15, 4);
            $table->decimal('quantity_shipped', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_transfers');
    }
};
