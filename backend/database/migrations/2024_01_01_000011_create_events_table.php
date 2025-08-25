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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['meeting', 'maintenance', 'power_outage', 'water_outage', 'social_event', 'emergency'])->default('meeting');
            $table->enum('scope', ['all', 'block', 'floor', 'apartment', 'specific'])->default('all');
            $table->json('target_scope')->nullable(); // Phạm vi ảnh hưởng
            $table->datetime('start_time');
            $table->datetime('end_time')->nullable();
            $table->string('location')->nullable(); // Địa điểm
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->unsignedBigInteger('created_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['type', 'status']);
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
}; 