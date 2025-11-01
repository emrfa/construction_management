<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_type_work_item', function (Blueprint $table) {
            $table->foreignId('work_type_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('work_item_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->primary(['work_type_id', 'work_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_type_work_item');
    }
};