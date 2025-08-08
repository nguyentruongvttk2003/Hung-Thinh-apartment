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
        Schema::create('vote_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vote_id');
            $table->string('option_text'); // Nội dung tùy chọn
            $table->text('description')->nullable(); // Mô tả chi tiết
            $table->integer('vote_count')->default(0); // Số phiếu bầu
            $table->integer('sort_order')->default(0); // Thứ tự hiển thị
            $table->timestamps();
            
            $table->foreign('vote_id')->references('id')->on('votes')->onDelete('cascade');
            $table->index(['vote_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_options');
    }
}; 