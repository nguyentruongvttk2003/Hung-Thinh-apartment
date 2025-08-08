package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "votes")
data class Vote(
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
    
    @SerializedName("status")
    val status: String,
    
    @SerializedName("total_votes")
    val totalVotes: Int,
    
    @SerializedName("participation_rate")
    val participationRate: Double,
    
    @SerializedName("has_voted")
    val hasVoted: Boolean = false,
    
    @SerializedName("options")
    val options: List<VoteOption>,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun getTypeDisplayName(): String {
        return when (type) {
            "decision" -> "Quyết định chung"
            "choice" -> "Lựa chọn"
            "rating" -> "Đánh giá"
            else -> type
        }
    }
    
    fun getStatusDisplayName(): String {
        return when (status) {
            "active" -> "Đang diễn ra"
            "completed" -> "Đã kết thúc"
            "cancelled" -> "Đã hủy"
            else -> status
        }
    }
    
    fun getStatusColor(): Int {
        return when (status) {
            "active" -> android.graphics.Color.GREEN
            "completed" -> android.graphics.Color.BLUE
            "cancelled" -> android.graphics.Color.GRAY
            else -> android.graphics.Color.BLACK
        }
    }
    
    fun isActive(): Boolean = status == "active"
    fun isCompleted(): Boolean = status == "completed"
    fun isCancelled(): Boolean = status == "cancelled"
}

data class VoteOption(
    val id: Int,
    val text: String,
    val votes_count: Int,
    val percentage: Double
) 