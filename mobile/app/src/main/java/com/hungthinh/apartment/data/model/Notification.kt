package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "notifications")
data class Notification(
    @PrimaryKey
    val id: Int,
    
    @SerializedName("title")
    val title: String,
    
    @SerializedName("content")
    val content: String,
    
    @SerializedName("type")
    val type: String,
    
    @SerializedName("target_type")
    val targetType: String,
    
    @SerializedName("target_id")
    val targetId: Int?,
    
    @SerializedName("is_read")
    val isRead: Boolean = false,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun getTypeDisplayName(): String {
        return when (type) {
            "general" -> "Thông báo chung"
            "maintenance" -> "Bảo trì"
            "payment" -> "Thanh toán"
            "event" -> "Sự kiện"
            "emergency" -> "Khẩn cấp"
            else -> type
        }
    }
    
    fun getTargetTypeDisplayName(): String {
        return when (targetType) {
            "all" -> "Tất cả cư dân"
            "block" -> "Theo block"
            "floor" -> "Theo tầng"
            "apartment" -> "Theo căn hộ"
            else -> targetType
        }
    }
    
    fun getFormattedDate(): String {
        // TODO: Implement date formatting
        return createdAt
    }
}

data class NotificationRequest(
    @SerializedName("title")
    val title: String,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("type")
    val type: String,
    
    @SerializedName("priority")
    val priority: String,
    
    @SerializedName("recipient_type")
    val recipientType: String,
    
    @SerializedName("recipient_ids")
    val recipientIds: List<Int>? = null,
    
    @SerializedName("apartment_ids")
    val apartmentIds: List<Int>? = null,
    
    @SerializedName("expires_at")
    val expiresAt: String? = null
) 