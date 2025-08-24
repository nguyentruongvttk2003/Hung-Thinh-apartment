<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->enum('type', ['pool','gym','tennis','bbq','meeting_room','parking','other'])->default('other');
            $table->enum('status', ['active','maintenance','inactive'])->default('active');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->integer('capacity')->nullable();
            $table->integer('booking_slot_minutes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};


