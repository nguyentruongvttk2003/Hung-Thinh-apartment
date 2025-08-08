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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('apartment_number')->unique(); // Số căn hộ: A1-101, B2-205
            $table->string('block')->nullable(); // Block: A, B, C
            $table->integer('floor'); // Tầng: 1, 2, 3...
            $table->integer('room_number'); // Số phòng: 101, 102...
            $table->decimal('area', 8, 2); // Diện tích (m2)
            $table->integer('bedrooms')->default(1); // Số phòng ngủ
            $table->enum('type', ['studio', '1BR', '2BR', '3BR', 'penthouse'])->default('1BR');
            $table->enum('status', ['occupied', 'vacant', 'maintenance', 'reserved'])->default('vacant');
            $table->unsignedBigInteger('owner_id')->nullable(); // Chủ hộ
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['block', 'floor']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
}; 