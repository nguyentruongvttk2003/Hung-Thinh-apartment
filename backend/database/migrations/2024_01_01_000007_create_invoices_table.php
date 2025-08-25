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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Số hóa đơn
            $table->unsignedBigInteger('apartment_id');
            $table->date('billing_period_start'); // Kỳ tính phí từ
            $table->date('billing_period_end'); // Kỳ tính phí đến
            $table->date('due_date'); // Ngày hạn thanh toán
            $table->decimal('management_fee', 12, 2)->default(0); // Phí quản lý
            $table->decimal('electricity_fee', 12, 2)->default(0); // Phí điện
            $table->decimal('water_fee', 12, 2)->default(0); // Phí nước
            $table->decimal('parking_fee', 12, 2)->default(0); // Phí gửi xe
            $table->decimal('other_fees', 12, 2)->default(0); // Phí khác
            $table->decimal('total_amount', 12, 2); // Tổng tiền
            $table->decimal('paid_amount', 12, 2)->default(0); // Số tiền đã thanh toán
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by'); // Kế toán tạo hóa đơn
            $table->timestamps();
            
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['apartment_id', 'billing_period_start']);
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
}; 