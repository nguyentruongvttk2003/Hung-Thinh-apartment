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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['general_meeting', 'budget_approval', 'rule_change', 'facility_upgrade', 'other'])->default('general_meeting');
            $table->enum('scope', ['all', 'block', 'floor', 'apartment'])->default('all');
            $table->json('target_scope')->nullable(); // Phạm vi biểu quyết
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->enum('status', ['draft', 'active', 'closed', 'cancelled'])->default('draft');
            $table->boolean('require_quorum')->default(true); // Yêu cầu đủ số phiếu
            $table->integer('quorum_percentage')->default(50); // Phần trăm tối thiểu
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
        Schema::dropIfExists('votes');
    }
}; 