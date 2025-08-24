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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'maintenance', 'payment', 'event', 'emergency'])->default('general');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('scope', ['all', 'block', 'floor', 'apartment', 'specific'])->default('all');
            $table->json('target_scope')->nullable(); // ['block' => 'A', 'floor' => 5, 'apartments' => [1,2,3]]
            $table->unsignedBigInteger('created_by'); // Người tạo thông báo
            $table->timestamp('scheduled_at')->nullable(); // Lịch gửi
            $table->timestamp('sent_at')->nullable(); // Thời gian đã gửi
            $table->enum('status', ['draft', 'scheduled', 'sent', 'cancelled'])->default('draft');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['type', 'status']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
}; 