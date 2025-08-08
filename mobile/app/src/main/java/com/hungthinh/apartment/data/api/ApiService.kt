package com.hungthinh.apartment.data.api

import com.hungthinh.apartment.data.model.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {
    
    // Authentication endpoints
    @POST("auth/login")
    suspend fun login(@Body request: LoginRequest): Response<ApiResponse<LoginResponse>>
    
    @POST("auth/logout")
    suspend fun logout(): Response<ApiResponse<Any>>
    
    @POST("auth/refresh")
    suspend fun refreshToken(): Response<ApiResponse<LoginResponse>>
    
    @GET("auth/me")
    suspend fun getCurrentUser(): Response<ApiResponse<User>>
    
    // Dashboard endpoints
    @GET("dashboard/stats")
    suspend fun getDashboardStats(): Response<ApiResponse<DashboardStats>>
    
    @GET("dashboard/recent-activities")
    suspend fun getRecentActivities(@Query("limit") limit: Int = 10): Response<ApiResponse<List<RecentActivity>>>
    
    // Notifications endpoints
    @GET("notifications")
    suspend fun getNotifications(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20,
        @Query("type") type: String? = null,
        @Query("is_read") isRead: Boolean? = null
    ): Response<ApiResponse<PaginatedData<Notification>>>
    
    @GET("notifications/{id}")
    suspend fun getNotification(@Path("id") id: Int): Response<ApiResponse<Notification>>
    
    @POST("notifications")
    suspend fun createNotification(@Body request: NotificationRequest): Response<ApiResponse<Notification>>
    
    @PUT("notifications/{id}/mark-read")
    suspend fun markNotificationAsRead(@Path("id") id: Int): Response<ApiResponse<Any>>
    
    @PUT("notifications/mark-all-read")
    suspend fun markAllNotificationsAsRead(): Response<ApiResponse<Any>>
    
    @DELETE("notifications/{id}")
    suspend fun deleteNotification(@Path("id") id: Int): Response<ApiResponse<Any>>
    
    // Invoices endpoints
    @GET("invoices")
    suspend fun getInvoices(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20,
        @Query("status") status: String? = null,
        @Query("type") type: String? = null,
        @Query("apartment_id") apartmentId: Int? = null
    ): Response<ApiResponse<PaginatedData<Invoice>>>
    
    @GET("invoices/{id}")
    suspend fun getInvoice(@Path("id") id: Int): Response<ApiResponse<Invoice>>
    
    @POST("invoices/{id}/pay")
    suspend fun payInvoice(
        @Path("id") id: Int,
        @Body request: PaymentRequest
    ): Response<ApiResponse<Invoice>>
    
    @GET("invoices/summary")
    suspend fun getInvoiceSummary(): Response<ApiResponse<Map<String, Any>>>
    
    // Feedback endpoints
    @GET("feedback")
    suspend fun getFeedback(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20,
        @Query("category") category: String? = null,
        @Query("status") status: String? = null,
        @Query("priority") priority: String? = null
    ): Response<ApiResponse<PaginatedData<Feedback>>>
    
    @GET("feedback/{id}")
    suspend fun getFeedbackById(@Path("id") id: Int): Response<ApiResponse<Feedback>>
    
    @POST("feedback")
    suspend fun createFeedback(@Body request: FeedbackRequest): Response<ApiResponse<Feedback>>
    
    @PUT("feedback/{id}")
    suspend fun updateFeedback(
        @Path("id") id: Int,
        @Body request: FeedbackRequest
    ): Response<ApiResponse<Feedback>>
    
    @DELETE("feedback/{id}")
    suspend fun deleteFeedback(@Path("id") id: Int): Response<ApiResponse<Any>>
    
    @POST("feedback/{id}/rate")
    suspend fun rateFeedback(
        @Path("id") id: Int,
        @Body rating: Map<String, Int>
    ): Response<ApiResponse<Any>>
    
    // User profile endpoints
    @PUT("user/profile")
    suspend fun updateProfile(@Body user: User): Response<ApiResponse<User>>
    
    @POST("user/change-password")
    suspend fun changePassword(@Body request: Map<String, String>): Response<ApiResponse<Any>>
    
    @POST("user/upload-avatar")
    suspend fun uploadAvatar(@Body avatar: Map<String, String>): Response<ApiResponse<User>>
    
    // Apartment endpoints
    @GET("apartments/my")
    suspend fun getMyApartment(): Response<ApiResponse<Any>>
    
    @GET("apartments/{id}")
    suspend fun getApartment(@Path("id") id: Int): Response<ApiResponse<Any>>
}
