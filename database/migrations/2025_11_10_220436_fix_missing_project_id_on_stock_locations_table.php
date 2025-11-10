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
        if (!Schema::hasColumn('stock_locations', 'project_id')) {

        Schema::table('stock_locations', function (Blueprint $table) {
            $table->foreignId('project_id')
                  ->nullable()
                  ->after('type') // Or wherever you want it
                  ->constrained('projects')
                  ->nullOnDelete();
                  });
                }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
