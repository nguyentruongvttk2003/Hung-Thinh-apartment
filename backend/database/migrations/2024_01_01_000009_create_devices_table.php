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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên thiết bị
            $table->string('device_code')->unique(); // Mã thiết bị
            $table->enum('category', ['elevator', 'generator', 'water_pump', 'air_conditioner', 'lighting', 'security', 'other'])->default('other');
            $table->string('location'); // Vị trí lắp đặt
            $table->string('brand')->nullable(); // Thương hiệu
            $table->string('model')->nullable(); // Model
            $table->date('installation_date'); // Ngày lắp đặt
            $table->date('warranty_expiry')->nullable(); // Ngày hết hạn bảo hành
            $table->enum('status', ['active', 'inactive', 'maintenance', 'broken'])->default('active');
            $table->text('specifications')->nullable(); // Thông số kỹ thuật
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('responsible_technician')->nullable(); // Kỹ thuật viên phụ trách
            $table->timestamps();
            
            $table->foreign('responsible_technician')->references('id')->on('users')->onDelete('set null');
            $table->index(['category', 'status']);
            $table->index('responsible_technician');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
}; 