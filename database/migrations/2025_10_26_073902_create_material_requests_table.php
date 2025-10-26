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
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by_user_id')->constrained('users')->onDelete('cascade'); // User who made request
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // User who approved
            $table->date('request_date');
            $table->date('required_date')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'partially_fulfilled', 'fulfilled', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
