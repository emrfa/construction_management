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
        Schema::table('unit_rate_analyses', function (Blueprint $table) {
            $table->decimal('overhead_profit_percentage', 5, 2)->default(0)->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_rate_analyses', function (Blueprint $table) {
            //
        });
    }
};
