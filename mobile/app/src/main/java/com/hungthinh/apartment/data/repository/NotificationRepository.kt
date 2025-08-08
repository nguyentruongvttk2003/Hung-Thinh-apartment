package com.hungthinh.apartment.data.repository

import com.hungthinh.apartment.data.api.ApiService
import com.hungthinh.apartment.data.model.*
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class NotificationRepository @Inject constructor(
    private val apiService: ApiService
) {
    
    fun getNotifications(
        page: Int = 1,
        perPage: Int = 20,
        type: String? = null,
        isRead: Boolean? = null
    ): Flow<Result<PaginatedData<Notification>>> = flow {
        try {
            val response = apiService.getNotifications(page, perPage, type, isRead)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val data = response.body()!!.data!!
                emit(Result.success(data))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get notifications"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getNotification(id: Int): Flow<Result<Notification>> = flow {
        try {
            val response = apiService.getNotification(id)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val notification = response.body()!!.data!!
                emit(Result.success(notification))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get notification"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun markAsRead(id: Int): Flow<Result<Boolean>> = flow {
        try {
            val response = apiService.markNotificationAsRead(id)
            
            if (response.isSuccessful && response.body()?.success == true) {
                emit(Result.success(true))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to mark as read"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun markAllAsRead(): Flow<Result<Boolean>> = flow {
        try {
            val response = apiService.markAllNotificationsAsRead()
            
            if (response.isSuccessful && response.body()?.success == true) {
                emit(Result.success(true))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to mark all as read"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun deleteNotification(id: Int): Flow<Result<Boolean>> = flow {
        try {
            val response = apiService.deleteNotification(id)
            
            if (response.isSuccessful && response.body()?.success == true) {
                emit(Result.success(true))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to delete notification"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
}
