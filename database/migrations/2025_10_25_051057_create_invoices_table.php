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
        Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('billing_id')->constrained()->onDelete('cascade'); // Link to Billing
        $table->foreignId('client_id')->constrained()->onDelete('cascade'); // Link to Client
        $table->string('invoice_no')->unique();
        $table->decimal('amount', 15, 2); // Base amount from billing
        $table->decimal('tax_amount', 15, 2)->default(0);
        $table->decimal('total_amount', 15, 2); // Amount + Tax
        $table->enum('status', ['draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled'])->default('draft');
        $table->date('issued_date');
        $table->date('due_date')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
