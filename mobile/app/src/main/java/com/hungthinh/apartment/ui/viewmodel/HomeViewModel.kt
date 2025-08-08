package com.hungthinh.apartment.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.hungthinh.apartment.data.model.*
import com.hungthinh.apartment.data.repository.DashboardRepository
import com.hungthinh.apartment.data.repository.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val dashboardRepository: DashboardRepository,
    private val authRepository: AuthRepository
) : ViewModel() {
    
    private val _uiState = MutableStateFlow(HomeUiState())
    val uiState: StateFlow<HomeUiState> = _uiState.asStateFlow()
    
    private val _dashboardStats = MutableStateFlow<DashboardStats?>(null)
    val dashboardStats: StateFlow<DashboardStats?> = _dashboardStats.asStateFlow()
    
    private val _recentActivities = MutableStateFlow<List<RecentActivity>>(emptyList())
    val recentActivities: StateFlow<List<RecentActivity>> = _recentActivities.asStateFlow()
    
    private val _currentUser = MutableStateFlow<User?>(null)
    val currentUser: StateFlow<User?> = _currentUser.asStateFlow()
    
    init {
        loadDashboardData()
        loadCurrentUser()
    }
    
    fun loadDashboardData() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)
            
            try {
                // Load dashboard stats
                dashboardRepository.getDashboardStats().collect { result ->
                    result.fold(
                        onSuccess = { stats ->
                            _dashboardStats.value = stats
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(
                                error = error.message,
                                isLoading = false
                            )
                        }
                    )
                }
                
                // Load recent activities
                dashboardRepository.getRecentActivities(10).collect { result ->
                    result.fold(
                        onSuccess = { activities ->
                            _recentActivities.value = activities
                            _uiState.value = _uiState.value.copy(isLoading = false)
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(
                                error = error.message,
                                isLoading = false
                            )
                        }
                    )
                }
                
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    error = e.message,
                    isLoading = false
                )
            }
        }
    }
    
    private fun loadCurrentUser() {
        val user = authRepository.getCurrentUserLocal()
        _currentUser.value = user
    }
    
    fun refreshData() {
        loadDashboardData()
    }
    
    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null)
    }
}

data class HomeUiState(
    val isLoading: Boolean = false,
    val error: String? = null
)
