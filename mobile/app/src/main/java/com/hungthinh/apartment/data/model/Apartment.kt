package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "apartments")
data class Apartment(
    @PrimaryKey
    val id: Int,
    
    @SerializedName("apartment_number")
    val apartmentNumber: String,
    
    @SerializedName("block")
    val block: String,
    
    @SerializedName("floor")
    val floor: Int,
    
    @SerializedName("area")
    val area: Double,
    
    @SerializedName("type")
    val type: String,
    
    @SerializedName("status")
    val status: String,
    
    @SerializedName("description")
    val description: String?,
    
    @SerializedName("owner_id")
    val ownerId: Int?,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun getTypeDisplayName(): String {
        return when (type) {
            "1BR" -> "1 phòng ngủ"
            "2BR" -> "2 phòng ngủ"
            "3BR" -> "3 phòng ngủ"
            "duplex" -> "Duplex"
            else -> type
        }
    }
    
    fun getStatusDisplayName(): String {
        return when (status) {
            "rented" -> "Đang cho thuê"
            "vacant" -> "Trống"
            "maintenance" -> "Bảo trì"
            "sold" -> "Đã bán"
            else -> status
        }
    }
    
    fun getStatusColor(): Int {
        return when (status) {
            "rented" -> android.graphics.Color.GREEN
            "vacant" -> android.graphics.Color.BLUE
            "maintenance" -> android.graphics.Color.YELLOW
            "sold" -> android.graphics.Color.GRAY
            else -> android.graphics.Color.BLACK
        }
    }
} 