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
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->dropColumn('percent_complete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->decimal('percent_complete', 5, 2)->default(0)->after('date');
        });
    }
};
