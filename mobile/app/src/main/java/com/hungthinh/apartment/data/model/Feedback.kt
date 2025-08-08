package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "feedbacks")
data class Feedback(
    @PrimaryKey
    val id: Int,
    
    @SerializedName("title")
    val title: String,
    
    @SerializedName("content")
    val content: String,
    
    @SerializedName("type")
    val type: String,
    
    @SerializedName("priority")
    val priority: String,
    
    @SerializedName("status")
    val status: String,
    
    @SerializedName("user_id")
    val userId: Int,
    
    @SerializedName("user_name")
    val userName: String,
    
    @SerializedName("apartment_id")
    val apartmentId: Int,
    
    @SerializedName("apartment_number")
    val apartmentNumber: String,
    
    @SerializedName("assigned_to")
    val assignedTo: String?,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun getTypeDisplayName(): String {
        return when (type) {
            "maintenance" -> "Bảo trì"
            "complaint" -> "Khiếu nại"
            "suggestion" -> "Đề xuất"
            "emergency" -> "Khẩn cấp"
            "other" -> "Khác"
            else -> type
        }
    }
    
    fun getPriorityDisplayName(): String {
        return when (priority) {
            "low" -> "Thấp"
            "medium" -> "Trung bình"
            "high" -> "Cao"
            "urgent" -> "Khẩn cấp"
            else -> priority
        }
    }
    
    fun getStatusDisplayName(): String {
        return when (status) {
            "pending" -> "Chờ xử lý"
            "in_progress" -> "Đang xử lý"
            "completed" -> "Đã hoàn thành"
            "cancelled" -> "Đã hủy"
            else -> status
        }
    }
    
    fun getPriorityColor(): Int {
        return when (priority) {
            "low" -> android.graphics.Color.GREEN
            "medium" -> android.graphics.Color.YELLOW
            "high" -> android.graphics.Color.RED
            "urgent" -> android.graphics.Color.MAGENTA
            else -> android.graphics.Color.BLACK
        }
    }
    
    fun getStatusColor(): Int {
        return when (status) {
            "pending" -> android.graphics.Color.YELLOW
            "in_progress" -> android.graphics.Color.BLUE
            "completed" -> android.graphics.Color.GREEN
            "cancelled" -> android.graphics.Color.GRAY
            else -> android.graphics.Color.BLACK
        }
    }
    
    fun isPending(): Boolean = status == "pending"
    fun isInProgress(): Boolean = status == "in_progress"
    fun isCompleted(): Boolean = status == "completed"
    fun isUrgent(): Boolean = priority == "urgent"
}

data class FeedbackRequest(
    @SerializedName("category")
    val category: String,
    
    @SerializedName("priority")
    val priority: String,
    
    @SerializedName("title")
    val title: String,
    
    @SerializedName("description")
    val description: String,
    
    @SerializedName("location")
    val location: String?,
    
    @SerializedName("contact_preferred")
    val contactPreferred: String?,
    
    @SerializedName("urgency_level")
    val urgencyLevel: Int = 1,
    
    @SerializedName("attachments")
    val attachments: List<String>? = null
) 