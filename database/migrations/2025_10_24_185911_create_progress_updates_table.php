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
    Schema::create('progress_updates', function (Blueprint $table) {
        $table->id();

        // Links to the WBS task (e.g., "Pengecoran...")
        $table->foreignId('quotation_item_id')->constrained()->onDelete('cascade');

        // Links to the user who made the log
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        $table->date('date'); // When the work was done

        // This stores the *new* total percentage for that task
        // e.g., If it was 20% and they log 30%, this stores "30".
        $table->decimal('percent_complete', 5, 2)->default(0); 

        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_updates');
    }
};
