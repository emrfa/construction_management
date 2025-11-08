<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Make sure to import this!

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This will change the 'status' column to allow 'received'
        // We set the default to 'draft' as you wanted.
        DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN status ENUM('draft', 'received') NOT NULL DEFAULT 'draft'");
        
        // This makes sure any old 'posted' values are updated to 'received'
        DB::table('goods_receipts')
            ->where('status', 'posted')
            ->update(['status' => 'received']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This reverts it back, just in case
        DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN status ENUM('draft', 'posted') NOT NULL DEFAULT 'draft'");
        
        // Revert the data
        DB::table('goods_receipts')
            ->where('status', 'received')
            ->update(['status' => 'posted']);
    }
};