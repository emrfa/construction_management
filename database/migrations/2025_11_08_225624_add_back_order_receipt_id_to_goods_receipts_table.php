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
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('back_order_receipt_id')
                  ->nullable()
                  ->after('status')
                  ->constrained('goods_receipts') // Self-referencing key
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['back_order_receipt_id']);
            $table->dropColumn('back_order_receipt_id');
        });
    }
};