package com.hungthinh.apartment.data.repository

import com.hungthinh.apartment.data.api.ApiService
import com.hungthinh.apartment.data.model.*
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class FeedbackRepository @Inject constructor(
    private val apiService: ApiService
) {
    
    fun getFeedback(
        page: Int = 1,
        perPage: Int = 20,
        category: String? = null,
        status: String? = null,
        priority: String? = null
    ): Flow<Result<PaginatedData<Feedback>>> = flow {
        try {
            val response = apiService.getFeedback(page, perPage, category, status, priority)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val data = response.body()!!.data!!
                emit(Result.success(data))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get feedback"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getFeedbackById(id: Int): Flow<Result<Feedback>> = flow {
        try {
            val response = apiService.getFeedbackById(id)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val feedback = response.body()!!.data!!
                emit(Result.success(feedback))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get feedback"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun createFeedback(feedbackRequest: FeedbackRequest): Flow<Result<Feedback>> = flow {
        try {
            val response = apiService.createFeedback(feedbackRequest)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val feedback = response.body()!!.data!!
                emit(Result.success(feedback))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to create feedback"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun updateFeedback(id: Int, feedbackRequest: FeedbackRequest): Flow<Result<Feedback>> = flow {
        try {
            val response = apiService.updateFeedback(id, feedbackRequest)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val feedback = response.body()!!.data!!
                emit(Result.success(feedback))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to update feedback"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun deleteFeedback(id: Int): Flow<Result<Boolean>> = flow {
        try {
            val response = apiService.deleteFeedback(id)
            
            if (response.isSuccessful && response.body()?.success == true) {
                emit(Result.success(true))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to delete feedback"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun rateFeedback(id: Int, rating: Int): Flow<Result<Boolean>> = flow {
        try {
            val request = mapOf("satisfaction_rating" to rating)
            val response = apiService.rateFeedback(id, request)
            
            if (response.isSuccessful && response.body()?.success == true) {
                emit(Result.success(true))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to rate feedback"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getMyFeedback(): Flow<Result<List<Feedback>>> = flow {
        try {
            val response = apiService.getFeedback(perPage = 100)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val data = response.body()!!.data!!.data
                emit(Result.success(data))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get my feedback"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
}
