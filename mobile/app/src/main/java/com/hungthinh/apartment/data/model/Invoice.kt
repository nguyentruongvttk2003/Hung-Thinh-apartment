package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "invoices")
data class Invoice(
    @PrimaryKey
    val id: Int,
    
    @SerializedName("invoice_number")
    val invoiceNumber: String,
    
    @SerializedName("apartment_id")
    val apartmentId: Int,
    
    @SerializedName("apartment_number")
    val apartmentNumber: String,
    
    @SerializedName("type")
    val type: String,
    
    @SerializedName("amount")
    val amount: Double,
    
    @SerializedName("due_date")
    val dueDate: String,
    
    @SerializedName("status")
    val status: String,
    
    @SerializedName("description")
    val description: String?,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun getTypeDisplayName(): String {
        return when (type) {
            "management_fee" -> "Phí quản lý"
            "electricity" -> "Tiền điện"
            "water" -> "Tiền nước"
            "parking" -> "Phí gửi xe"
            "other" -> "Khác"
            else -> type
        }
    }
    
    fun getStatusDisplayName(): String {
        return when (status) {
            "paid" -> "Đã thanh toán"
            "unpaid" -> "Chưa thanh toán"
            "overdue" -> "Quá hạn"
            "partial" -> "Thanh toán một phần"
            else -> status
        }
    }
    
    fun getStatusColor(): Int {
        return when (status) {
            "paid" -> android.graphics.Color.GREEN
            "unpaid" -> android.graphics.Color.BLUE
            "overdue" -> android.graphics.Color.RED
            "partial" -> android.graphics.Color.YELLOW
            else -> android.graphics.Color.BLACK
        }
    }
    
    fun isOverdue(): Boolean = status == "overdue"
    fun isPaid(): Boolean = status == "paid"
    fun isUnpaid(): Boolean = status == "unpaid"
    
    fun getFormattedAmount(): String {
        return String.format("%,.0f VNĐ", amount)
    }
}

data class PaymentRequest(
    @SerializedName("invoice_id")
    val invoiceId: Int,
    
    @SerializedName("payment_method")
    val paymentMethod: String,
    
    @SerializedName("payment_reference")
    val paymentReference: String?,
    
    @SerializedName("notes")
    val notes: String?
) 