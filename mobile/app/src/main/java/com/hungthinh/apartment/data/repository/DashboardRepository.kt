package com.hungthinh.apartment.data.repository

import com.hungthinh.apartment.data.api.ApiService
import com.hungthinh.apartment.data.model.*
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class DashboardRepository @Inject constructor(
    private val apiService: ApiService
) {
    
    fun getDashboardStats(): Flow<Result<DashboardStats>> = flow {
        try {
            val response = apiService.getDashboardStats()
            
            if (response.isSuccessful && response.body()?.success == true) {
                val stats = response.body()!!.data!!
                emit(Result.success(stats))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get dashboard stats"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getRecentActivities(limit: Int = 10): Flow<Result<List<RecentActivity>>> = flow {
        try {
            val response = apiService.getRecentActivities(limit)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val activities = response.body()!!.data!!
                emit(Result.success(activities))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get recent activities"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
}
