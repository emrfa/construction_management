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
        Schema::table('work_items', function (Blueprint $table) {
            try {
                 $table->dropForeign(['work_type_id']);
            } catch (\Exception $e) {
                // No foreign key to drop, continue
            }
           
            // 2. This is the important part: make the column nullable
            $table->unsignedBigInteger('work_type_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_items', function (Blueprint $table) {
            $table->unsignedBigInteger('work_type_id')->nullable(false)->change();
        });
    }
};
