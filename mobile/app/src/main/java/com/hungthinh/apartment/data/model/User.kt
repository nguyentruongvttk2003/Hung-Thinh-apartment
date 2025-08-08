package com.hungthinh.apartment.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "users")
data class User(
    @PrimaryKey
    val id: Int,
    
    @SerializedName("name")
    val name: String,
    
    @SerializedName("email")
    val email: String,
    
    @SerializedName("phone")
    val phone: String?,
    
    @SerializedName("role")
    val role: String,
    
    @SerializedName("apartment_id")
    val apartmentId: Int?,
    
    @SerializedName("apartment_number")
    val apartmentNumber: String?,
    
    @SerializedName("avatar")
    val avatar: String?,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String
) {
    fun isResident(): Boolean = role == "resident"
    fun isAdmin(): Boolean = role == "admin"
    fun isAccountant(): Boolean = role == "accountant"
    fun isTechnician(): Boolean = role == "technician"
    
    fun getRoleDisplayName(): String {
        return when (role) {
            "resident" -> "Cư dân"
            "admin" -> "Quản trị viên"
            "accountant" -> "Kế toán"
            "technician" -> "Kỹ thuật viên"
            else -> role
        }
    }
} 