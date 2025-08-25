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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Người gửi phản ánh
            $table->unsignedBigInteger('apartment_id')->nullable(); // Căn hộ liên quan
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['complaint', 'suggestion', 'maintenance_request', 'general'])->default('general');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed', 'rejected'])->default('pending');
            $table->unsignedBigInteger('assigned_to')->nullable(); // Kỹ thuật viên được phân công
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->integer('rating')->nullable(); // Đánh giá của cư dân (1-5)
            $table->text('rating_comment')->nullable();
            $table->json('attachments')->nullable(); // Hình ảnh đính kèm
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['type', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
}; 