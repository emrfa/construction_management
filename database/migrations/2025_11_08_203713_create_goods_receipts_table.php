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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
    $table->string('receipt_no')->unique(); // We'll auto-generate this
    $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->date('receipt_date');
    $table->enum('status', ['draft', 'posted'])->default('posted');
    $table->text('notes')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
