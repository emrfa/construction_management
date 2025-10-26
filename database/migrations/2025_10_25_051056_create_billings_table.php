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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
        $table->foreignId('project_id')->constrained()->onDelete('cascade');
        $table->string('billing_no')->unique();
        $table->decimal('amount', 15, 2);
        $table->enum('status', ['pending', 'approved', 'invoiced', 'rejected'])->default('pending');
        $table->date('billing_date');
        $table->text('notes')->nullable(); // e.g., "For Milestone 1: Foundation"
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
