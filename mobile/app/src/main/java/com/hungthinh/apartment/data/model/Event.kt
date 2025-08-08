package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "events")
data class Event(
    @PrimaryKey
    val id: Int,
    
    @SerializedName("title")
    val title: String,
    
    @SerializedName("description")
    val description: String,
    
    @SerializedName("type")
    val type: String,
    
    @SerializedName("start_date")
    val startDate: String,
    
    @SerializedName("end_date")
    val endDate: String,
    
    @SerializedName("location")
    val location: String,
    
    @SerializedName("status")
    val status: String,
    
    @SerializedName("max_participants")
    val maxParticipants: Int,
    
    @SerializedName("participants_count")
    val participantsCount: Int,
    
    @SerializedName("is_registered")
    val isRegistered: Boolean = false,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun getTypeDisplayName(): String {
        return when (type) {
            "meeting" -> "Họp cư dân"
            "cultural" -> "Sự kiện văn hóa"
            "maintenance" -> "Bảo trì"
            "other" -> "Khác"
            else -> type
        }
    }
    
    fun getStatusDisplayName(): String {
        return when (status) {
            "upcoming" -> "Sắp diễn ra"
            "ongoing" -> "Đang diễn ra"
            "completed" -> "Đã kết thúc"
            "cancelled" -> "Đã hủy"
            else -> status
        }
    }
    
    fun getStatusColor(): Int {
        return when (status) {
            "upcoming" -> android.graphics.Color.BLUE
            "ongoing" -> android.graphics.Color.GREEN
            "completed" -> android.graphics.Color.GRAY
            "cancelled" -> android.graphics.Color.RED
            else -> android.graphics.Color.BLACK
        }
    }
    
    fun isUpcoming(): Boolean = status == "upcoming"
    fun isOngoing(): Boolean = status == "ongoing"
    fun isCompleted(): Boolean = status == "completed"
    fun isCancelled(): Boolean = status == "cancelled"
    
    fun getAvailableSpots(): Int = maxParticipants - participantsCount
    fun isFull(): Boolean = participantsCount >= maxParticipants
} 