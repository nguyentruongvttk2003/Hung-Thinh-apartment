package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "payments")
data class Payment(
    @PrimaryKey
    val id: Int,
    
    @SerializedName("invoice_id")
    val invoiceId: Int,
    
    @SerializedName("invoice_number")
    val invoiceNumber: String,
    
    @SerializedName("amount")
    val amount: Double,
    
    @SerializedName("payment_method")
    val paymentMethod: String,
    
    @SerializedName("reference_number")
    val referenceNumber: String?,
    
    @SerializedName("status")
    val status: String,
    
    @SerializedName("payment_date")
    val paymentDate: String,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun getPaymentMethodDisplayName(): String {
        return when (paymentMethod) {
            "qr" -> "Thanh toán QR"
            "bank_transfer" -> "Chuyển khoản"
            "cash" -> "Tiền mặt"
            else -> paymentMethod
        }
    }
    
    fun getStatusDisplayName(): String {
        return when (status) {
            "pending" -> "Chờ xử lý"
            "completed" -> "Hoàn thành"
            "failed" -> "Thất bại"
            "cancelled" -> "Đã hủy"
            else -> status
        }
    }
    
    fun getStatusColor(): Int {
        return when (status) {
            "pending" -> android.graphics.Color.YELLOW
            "completed" -> android.graphics.Color.GREEN
            "failed" -> android.graphics.Color.RED
            "cancelled" -> android.graphics.Color.GRAY
            else -> android.graphics.Color.BLACK
        }
    }
    
    fun isPending(): Boolean = status == "pending"
    fun isCompleted(): Boolean = status == "completed"
    fun isFailed(): Boolean = status == "failed"
    fun isCancelled(): Boolean = status == "cancelled"
    
    fun getFormattedAmount(): String {
        return String.format("%,.0f VNĐ", amount)
    }
} 