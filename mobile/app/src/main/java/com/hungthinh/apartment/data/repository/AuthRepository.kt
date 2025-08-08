package com.hungthinh.apartment.data.repository

import com.hungthinh.apartment.data.api.ApiService
import com.hungthinh.apartment.data.local.PreferenceManager
import com.hungthinh.apartment.data.model.*
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import retrofit2.Response
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AuthRepository @Inject constructor(
    private val apiService: ApiService,
    private val preferenceManager: PreferenceManager
) {
    
    fun login(email: String, password: String): Flow<Result<LoginResponse>> = flow {
        try {
            val request = LoginRequest(email, password)
            val response = apiService.login(request)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val loginResponse = response.body()!!.data!!
                
                // Save token and user info
                preferenceManager.saveAccessToken(loginResponse.accessToken)
                preferenceManager.saveUser(loginResponse.user)
                
                emit(Result.success(loginResponse))
            } else {
                val errorMessage = response.body()?.message ?: "Login failed"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun logout(): Flow<Result<Boolean>> = flow {
        try {
            val response = apiService.logout()
            
            // Clear local data regardless of API response
            preferenceManager.clearAll()
            
            if (response.isSuccessful) {
                emit(Result.success(true))
            } else {
                // Still consider it successful since we cleared local data
                emit(Result.success(true))
            }
        } catch (e: Exception) {
            // Clear local data even if API call fails
            preferenceManager.clearAll()
            emit(Result.success(true))
        }
    }
    
    fun getCurrentUser(): Flow<Result<User>> = flow {
        try {
            val response = apiService.getCurrentUser()
            
            if (response.isSuccessful && response.body()?.success == true) {
                val user = response.body()!!.data!!
                preferenceManager.saveUser(user)
                emit(Result.success(user))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get user info"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun refreshToken(): Flow<Result<LoginResponse>> = flow {
        try {
            val response = apiService.refreshToken()
            
            if (response.isSuccessful && response.body()?.success == true) {
                val loginResponse = response.body()!!.data!!
                
                // Update token and user info
                preferenceManager.saveAccessToken(loginResponse.accessToken)
                preferenceManager.saveUser(loginResponse.user)
                
                emit(Result.success(loginResponse))
            } else {
                // Token refresh failed, need to login again
                preferenceManager.clearAll()
                val errorMessage = response.body()?.message ?: "Token refresh failed"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            // Token refresh failed, need to login again
            preferenceManager.clearAll()
            emit(Result.failure(e))
        }
    }
    
    fun isLoggedIn(): Boolean {
        return preferenceManager.getAccessToken() != null && 
               preferenceManager.getCurrentUser() != null
    }
    
    fun getCurrentUserLocal(): User? {
        return preferenceManager.getCurrentUser()
    }
}
