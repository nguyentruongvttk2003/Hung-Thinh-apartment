package com.hungthinh.apartment.data.model

import com.google.gson.annotations.SerializedName

// Generic API Response wrapper
data class ApiResponse<T>(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("message")
    val message: String?,
    
    @SerializedName("data")
    val data: T?,
    
    @SerializedName("errors")
    val errors: Map<String, List<String>>? = null,
    
    @SerializedName("meta")
    val meta: Meta? = null
)

// Pagination metadata
data class Meta(
    @SerializedName("current_page")
    val currentPage: Int,
    
    @SerializedName("last_page")
    val lastPage: Int,
    
    @SerializedName("per_page")
    val perPage: Int,
    
    @SerializedName("total")
    val total: Int,
    
    @SerializedName("from")
    val from: Int?,
    
    @SerializedName("to")
    val to: Int?
)

// Paginated data wrapper
data class PaginatedData<T>(
    @SerializedName("data")
    val data: List<T>,
    
    @SerializedName("current_page")
    val currentPage: Int,
    
    @SerializedName("last_page")
    val lastPage: Int,
    
    @SerializedName("per_page")
    val perPage: Int,
    
    @SerializedName("total")
    val total: Int,
    
    @SerializedName("from")
    val from: Int?,
    
    @SerializedName("to")
    val to: Int?
)

// Dashboard statistics
data class DashboardStats(
    @SerializedName("total_apartments")
    val totalApartments: Int,
    
    @SerializedName("occupied_apartments")
    val occupiedApartments: Int,
    
    @SerializedName("pending_invoices")
    val pendingInvoices: Int,
    
    @SerializedName("overdue_invoices")
    val overdueInvoices: Int,
    
    @SerializedName("total_residents")
    val totalResidents: Int,
    
    @SerializedName("active_maintenance")
    val activeMaintenance: Int,
    
    @SerializedName("unread_notifications")
    val unreadNotifications: Int,
    
    @SerializedName("pending_feedback")
    val pendingFeedback: Int,
    
    @SerializedName("monthly_revenue")
    val monthlyRevenue: String,
    
    @SerializedName("collection_rate")
    val collectionRate: Double
)

// Recent activities
data class RecentActivity(
    @SerializedName("id")
    val id: Int,
    
    @SerializedName("type")
    val type: String, // payment, maintenance, notification, feedback
    
    @SerializedName("title")
    val title: String,
    
    @SerializedName("description")
    val description: String,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("user_name")
    val userName: String?,
    
    @SerializedName("apartment_number")
    val apartmentNumber: String?
) {
    fun getTypeDisplayName(): String {
        return when (type) {
            "payment" -> "Thanh toán"
            "maintenance" -> "Bảo trì"
            "notification" -> "Thông báo"
            "feedback" -> "Phản hồi"
            else -> type
        }
    }
    
    fun getTypeIcon(): String {
        return when (type) {
            "payment" -> "💰"
            "maintenance" -> "🔧"
            "notification" -> "📢"
            "feedback" -> "💬"
            else -> "📋"
        }
    }
}

// Error response
data class ErrorResponse(
    @SerializedName("success")
    val success: Boolean = false,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("errors")
    val errors: Map<String, List<String>>? = null,
    
    @SerializedName("code")
    val code: String? = null
)
