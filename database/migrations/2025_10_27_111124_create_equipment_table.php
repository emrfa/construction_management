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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier')->unique()->nullable(); // Asset Code
            $table->string('type')->nullable();
            $table->enum('status', ['owned', 'rented', 'maintenance', 'disposed', 'pending_acquisition'])->default('pending_acquisition'); // Added 'pending'

            // --- Owned Details (Filled by PO Receiving) ---
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();

            // --- Rental Details ---
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->date('rental_start_date')->nullable();
            $table->date('rental_end_date')->nullable();
            $table->decimal('rental_rate', 15, 2)->nullable(); // Actual rate for current rental
            $table->string('rental_rate_unit')->nullable(); // Actual unit for current rental
            $table->string('rental_agreement_ref')->nullable(); // PO#, etc.

            // --- Base/Reference Pricing (Optional) ---
            $table->decimal('base_purchase_price', 15, 2)->nullable(); // Typical buy price
            $table->decimal('base_rental_rate', 15, 2)->nullable();    // Typical rent rate
            $table->string('base_rental_rate_unit')->nullable(); // Typical rent unit (day, hour)

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
