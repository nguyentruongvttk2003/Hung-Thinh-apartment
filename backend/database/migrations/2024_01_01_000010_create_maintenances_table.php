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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['preventive', 'corrective', 'emergency'])->default('preventive');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->date('scheduled_date'); // Ngày lên lịch bảo trì
            $table->time('scheduled_time')->nullable(); // Giờ bảo trì
            $table->timestamp('started_at')->nullable(); // Thời gian bắt đầu
            $table->timestamp('completed_at')->nullable(); // Thời gian hoàn thành
            $table->unsignedBigInteger('assigned_technician')->nullable(); // Kỹ thuật viên được phân công
            $table->text('work_performed')->nullable(); // Công việc đã thực hiện
            $table->text('parts_replaced')->nullable(); // Linh kiện thay thế
            $table->decimal('cost', 12, 2)->nullable(); // Chi phí bảo trì
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->foreign('assigned_technician')->references('id')->on('users')->onDelete('set null');
            $table->index(['device_id', 'status']);
            $table->index(['scheduled_date', 'status']);
            $table->index('assigned_technician');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
}; 