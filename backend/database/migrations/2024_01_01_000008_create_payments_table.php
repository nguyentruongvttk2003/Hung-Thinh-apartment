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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique(); // Số phiếu thanh toán
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_id'); // Người thanh toán
            $table->decimal('amount', 12, 2); // Số tiền thanh toán
            $table->enum('payment_method', ['cash', 'bank_transfer', 'qr_code', 'credit_card', 'e_wallet'])->default('cash');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable(); // Mã giao dịch từ cổng thanh toán
            $table->text('payment_details')->nullable(); // Chi tiết thanh toán
            $table->timestamp('paid_at')->nullable(); // Thời gian thanh toán
            $table->unsignedBigInteger('processed_by')->nullable(); // Người xử lý thanh toán
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['invoice_id', 'status']);
            $table->index(['user_id', 'paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}; 