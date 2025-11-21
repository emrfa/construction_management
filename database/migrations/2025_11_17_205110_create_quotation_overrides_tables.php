<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Material Overrides
        Schema::create('quotation_material_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->decimal('override_price', 15, 2);
            $table->timestamps();
            // Ensure one price per item per quotation
            $table->unique(['quotation_id', 'inventory_item_id'], 'q_mat_override_unique');
        });

        // 2. Labor Overrides
        Schema::create('quotation_labor_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->foreignId('labor_rate_id')->constrained('labor_rates')->onDelete('cascade');
            $table->decimal('override_price', 15, 2);
            $table->timestamps();
            $table->unique(['quotation_id', 'labor_rate_id'], 'q_lab_override_unique');
        });

        // 3. Equipment Overrides
        Schema::create('quotation_equipment_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            // Note: 'equipment' table name is singular in your schema
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->decimal('override_price', 15, 2);
            $table->timestamps();
            $table->unique(['quotation_id', 'equipment_id'], 'q_eq_override_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_equipment_overrides');
        Schema::dropIfExists('quotation_labor_overrides');
        Schema::dropIfExists('quotation_material_overrides');
    }
};