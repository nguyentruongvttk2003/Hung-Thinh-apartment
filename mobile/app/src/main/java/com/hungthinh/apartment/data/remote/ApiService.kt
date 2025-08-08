package com.hungthinh.apartment.data.remote

import com.hungthinh.apartment.data.model.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {
    
    // Authentication
    @POST("auth/login")
    suspend fun login(@Body request: LoginRequest): Response<AuthResponse>
    
    @POST("auth/register")
    suspend fun register(@Body request: RegisterRequest): Response<AuthResponse>
    
    @POST("auth/logout")
    suspend fun logout(): Response<BaseResponse>
    
    @GET("auth/profile")
    suspend fun getProfile(): Response<User>
    
    @PUT("auth/profile")
    suspend fun updateProfile(@Body request: UpdateProfileRequest): Response<User>
    
    @POST("auth/change-password")
    suspend fun changePassword(@Body request: ChangePasswordRequest): Response<BaseResponse>
    
    // Notifications
    @GET("notifications")
    suspend fun getNotifications(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20
    ): Response<PaginatedResponse<Notification>>
    
    @GET("notifications/{id}")
    suspend fun getNotification(@Path("id") id: Int): Response<Notification>
    
    @PUT("notifications/{id}/read")
    suspend fun markNotificationAsRead(@Path("id") id: Int): Response<BaseResponse>
    
    @GET("notifications/unread-count")
    suspend fun getUnreadNotificationCount(): Response<UnreadCountResponse>
    
    // Invoices
    @GET("invoices")
    suspend fun getInvoices(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20,
        @Query("status") status: String? = null
    ): Response<PaginatedResponse<Invoice>>
    
    @GET("invoices/{id}")
    suspend fun getInvoice(@Path("id") id: Int): Response<Invoice>
    
    @GET("invoices/pending")
    suspend fun getPendingInvoices(): Response<List<Invoice>>
    
    // Payments
    @POST("payments")
    suspend fun createPayment(@Body request: CreatePaymentRequest): Response<Payment>
    
    @GET("payments")
    suspend fun getPayments(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20
    ): Response<PaginatedResponse<Payment>>
    
    @GET("payments/{id}")
    suspend fun getPayment(@Path("id") id: Int): Response<Payment>
    
    // Feedback
    @GET("feedbacks")
    suspend fun getFeedbacks(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20,
        @Query("status") status: String? = null
    ): Response<PaginatedResponse<Feedback>>
    
    @POST("feedbacks")
    suspend fun createFeedback(@Body request: CreateFeedbackRequest): Response<Feedback>
    
    @GET("feedbacks/{id}")
    suspend fun getFeedback(@Path("id") id: Int): Response<Feedback>
    
    // Events
    @GET("events")
    suspend fun getEvents(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20
    ): Response<PaginatedResponse<Event>>
    
    @GET("events/{id}")
    suspend fun getEvent(@Path("id") id: Int): Response<Event>
    
    @POST("events/{id}/register")
    suspend fun registerForEvent(@Path("id") id: Int): Response<BaseResponse>
    
    // Votes
    @GET("votes")
    suspend fun getVotes(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20
    ): Response<PaginatedResponse<Vote>>
    
    @GET("votes/{id}")
    suspend fun getVote(@Path("id") id: Int): Response<Vote>
    
    @POST("votes/{id}/cast")
    suspend fun castVote(@Path("id") id: Int, @Body request: CastVoteRequest): Response<BaseResponse>
    
    // Dashboard
    @GET("dashboard")
    suspend fun getDashboardData(): Response<DashboardData>
}

// Request/Response Models
data class LoginRequest(
    val email: String,
    val password: String
)

data class RegisterRequest(
    val name: String,
    val email: String,
    val password: String,
    val password_confirmation: String,
    val phone: String? = null
)

data class AuthResponse(
    val user: User,
    val token: String,
    val message: String
)

data class BaseResponse(
    val message: String,
    val success: Boolean = true
)

data class UpdateProfileRequest(
    val name: String,
    val phone: String?,
    val address: String?
)

data class ChangePasswordRequest(
    val current_password: String,
    val new_password: String,
    val new_password_confirmation: String
)

data class PaginatedResponse<T>(
    val data: List<T>,
    val current_page: Int,
    val last_page: Int,
    val per_page: Int,
    val total: Int
)

data class UnreadCountResponse(
    val count: Int
)

data class CreatePaymentRequest(
    val invoice_id: Int,
    val amount: Double,
    val payment_method: String,
    val reference_number: String? = null
)

data class CreateFeedbackRequest(
    val title: String,
    val content: String,
    val type: String,
    val priority: String
)

data class CastVoteRequest(
    val option_id: Int
)

data class DashboardData(
    val total_invoices: Int,
    val pending_invoices: Int,
    val total_amount: Double,
    val recent_notifications: List<Notification>,
    val upcoming_events: List<Event>,
    val active_votes: List<Vote>
) 