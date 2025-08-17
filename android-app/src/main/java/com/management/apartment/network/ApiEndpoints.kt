package com.management.apartment.network

import retrofit2.Call
import retrofit2.http.*

data class LoginRequest(val email: String, val password: String)
data class LoginResponse(val success: Boolean, val data: TokenData?)
data class TokenData(val token: String, val token_type: String, val expires_in: Int)

data class UserProfile(val id: Long, val name: String?, val email: String?, val phone: String?, val role: String?, val status: String?)
data class MeResponse(val success: Boolean, val data: UserProfile?)

data class NotificationDto(val id: Long, val title: String, val content: String, val type: String, val priority: String)
data class InvoiceDto(val id: Long, val invoice_number: String, val total_amount: Double, val status: String)
data class EventDto(val id: Long, val title: String, val description: String?, val start_time: String?)
data class FeedbackDto(val id: Long, val subject: String, val description: String, val status: String)
    data class VoteOptionDto(val id: Long, val option_text: String)
    data class VoteDto(
        val id: Long,
        val title: String,
        val description: String?,
        val type: String?,
        val status: String,
        val options: List<VoteOptionDto>?
    )

data class AmenityDto(val id: Long, val name: String, val description: String?, val type: String)
data class AmenityAvailability(val success: Boolean, val data: AvailabilityData)
data class AvailabilityData(val amenity: AmenityDto, val slots: List<SlotDto>)
data class SlotDto(val start: String, val end: String, val available: Boolean)

data class BookingRequest(val amenity_id: Long, val start_time: String, val end_time: String, val notes: String?)
data class BookingResponse(val success: Boolean, val data: Any?)

data class QrPayloadResponse(val success: Boolean, val data: QrData)
data class QrData(val payment_id: Long, val qr_string: String)

interface ApiEndpoints {
    @POST("auth/login")
    fun login(@Body body: LoginRequest): Call<LoginResponse>

    @GET("auth/me")
    fun me(@Header("Authorization") token: String): Call<MeResponse>

    @POST("auth/logout")
    fun logout(@Header("Authorization") token: String): Call<GenericResponse<Any>>

    @POST("auth/update-profile")
    fun updateProfile(@Header("Authorization") token: String, @Body body: Map<String, @JvmSuppressWildcards Any?>): Call<GenericResponse<Any>>

    data class ChangePasswordRequest(val current_password: String, val new_password: String, val new_password_confirmation: String)
    @POST("auth/change-password")
    fun changePassword(@Header("Authorization") token: String, @Body body: ChangePasswordRequest): Call<GenericResponse<Any>>

    @GET("notifications")
    fun notifications(@Header("Authorization") token: String): Call<WrappedList<NotificationDto>>

    @PUT("notifications/mark-all-read")
    fun markAllNotificationsRead(@Header("Authorization") token: String): Call<GenericResponse<Any>>

    // My feedbacks
    @GET("feedbacks")
    fun feedbacks(@Header("Authorization") token: String, @Query("status") status: String? = null, @Query("per_page") perPage: Int? = 20): Call<WrappedList<FeedbackDto>>

    @GET("invoices")
    fun invoices(@Header("Authorization") token: String): Call<WrappedList<InvoiceDto>>

    @GET("invoices/{id}/qr")
    fun invoiceQr(@Header("Authorization") token: String, @Path("id") id: Long): Call<QrPayloadResponse>

    data class CreateInvoiceRequest(
        val apartment_id: Long,
        val month: Int,
        val year: Int,
        val management_fee: Double,
        val electricity_fee: Double,
        val water_fee: Double,
        val parking_fee: Double,
        val other_fees: Double?,
        val total_amount: Double,
        val due_date: String,
        val notes: String?
    )
    @POST("invoices")
    fun createInvoice(@Header("Authorization") token: String, @Body body: CreateInvoiceRequest): Call<GenericResponse<Any>>

    @GET("events/upcoming")
    fun events(@Header("Authorization") token: String): Call<GenericResponse<List<EventDto>>>

    @POST("feedbacks")
    fun createFeedback(@Header("Authorization") token: String, @Body body: Map<String, Any>): Call<GenericResponse<FeedbackDto>>

    @GET("votes/active")
    fun activeVotes(@Header("Authorization") token: String): Call<GenericResponse<List<VoteDto>>>

    @POST("votes/{id}/vote")
    fun submitVote(
        @Header("Authorization") token: String,
        @Path("id") id: Long,
        @Body body: Map<String, @JvmSuppressWildcards Any>
    ): Call<GenericResponse<Any>>

    @GET("amenities")
    fun amenities(@Header("Authorization") token: String): Call<GenericResponse<List<AmenityDto>>>

    @GET("amenities/{id}/availability")
    fun amenityAvailability(@Header("Authorization") token: String, @Path("id") id: Long, @Query("date") date: String): Call<AmenityAvailability>

    @POST("amenity-bookings")
    fun bookAmenity(@Header("Authorization") token: String, @Body body: BookingRequest): Call<BookingResponse>

    @GET("my-apartments")
    fun myApartments(@Header("Authorization") token: String): Call<List<ApartmentSimple>>

    @GET("apartments")
    fun apartments(@Header("Authorization") token: String): Call<WrappedList<ApartmentSimple>>

    @GET("apartments/{id}")
    fun apartment(@Header("Authorization") token: String, @Path("id") id: Long): Call<ApartmentDetail>

    @PUT("apartments/{id}")
    fun updateApartment(@Header("Authorization") token: String, @Path("id") id: Long, @Body body: Map<String, @JvmSuppressWildcards Any?>): Call<GenericResponse<ApartmentDetail>>

    @POST("apartments")
    fun createApartment(@Header("Authorization") token: String, @Body body: Map<String, @JvmSuppressWildcards Any?>): Call<GenericResponse<ApartmentDetail>>

    // Residents
    data class ResidentUserDto(val id: Long, val name: String?, val email: String?, val phone: String?)
    data class ResidentDto(
        val id: Long,
        val user_id: Long,
        val apartment_id: Long,
        val relationship: String,
        val move_in_date: String?,
        val status: String?,
        val is_primary_contact: Boolean?,
        val notes: String?,
        val user: ResidentUserDto?
    )

    @GET("apartments/{id}/residents")
    fun apartmentResidents(@Header("Authorization") token: String, @Path("id") apartmentId: Long): Call<List<ResidentDto>>

    @POST("apartments/{id}/residents")
    fun addResident(
        @Header("Authorization") token: String,
        @Path("id") apartmentId: Long,
        @Body body: Map<String, @JvmSuppressWildcards Any?>
    ): Call<GenericResponse<ResidentDto>>

    @DELETE("apartments/{apartment}/residents/{resident}")
    fun removeResident(
        @Header("Authorization") token: String,
        @Path("apartment") apartmentId: Long,
        @Path("resident") residentId: Long
    ): Call<GenericResponse<Any>>

    // Payments
    data class PaymentDto(val id: Long, val invoice_id: Long?, val amount: Double, val status: String, val payment_method: String?, val created_at: String?)
    @GET("my-payments")
    fun myPayments(@Header("Authorization") token: String): Call<List<PaymentDto>>
}

data class GenericResponse<T>(val success: Boolean, val data: T?)
data class WrappedList<T>(val success: Boolean, val data: List<T>?, val total: Int?, val per_page: Int?, val current_page: Int?, val last_page: Int?)

data class ApartmentSimple(
    val id: Long,
    val apartment_number: String?,
    val status: String? = null,
    val block: String? = null,
    val floor: Int? = null,
    val area: String? = null,
    val bedrooms: Int? = null,
    val owner_name: String? = null
)

data class ApartmentDetail(
    val id: Long,
    val apartment_number: String?,
    val block: String?,
    val floor: Int?,
    val room_number: Int?,
    val area: String?,
    val bedrooms: Int?,
    val type: String?,
    val status: String?,
    val description: String?
)


