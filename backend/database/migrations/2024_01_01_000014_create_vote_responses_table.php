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
        Schema::create('vote_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vote_id');
            $table->unsignedBigInteger('vote_option_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment')->nullable(); // Ý kiến bổ sung
            $table->timestamp('voted_at');
            $table->timestamps();
            
            $table->foreign('vote_id')->references('id')->on('votes')->onDelete('cascade');
            $table->foreign('vote_option_id')->references('id')->on('vote_options')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['vote_id', 'user_id']); // Mỗi người chỉ được bầu 1 lần
            $table->index(['vote_id', 'vote_option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_responses');
    }
}; 