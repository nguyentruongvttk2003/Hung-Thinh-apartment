package com.hungthinh.apartment.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.hungthinh.apartment.data.model.*
import com.hungthinh.apartment.data.repository.NotificationRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class NotificationsViewModel @Inject constructor(
    private val notificationRepository: NotificationRepository
) : ViewModel() {
    
    private val _uiState = MutableStateFlow(NotificationsUiState())
    val uiState: StateFlow<NotificationsUiState> = _uiState.asStateFlow()
    
    private val _notifications = MutableStateFlow<List<Notification>>(emptyList())
    val notifications: StateFlow<List<Notification>> = _notifications.asStateFlow()
    
    private val _selectedFilter = MutableStateFlow("all")
    val selectedFilter: StateFlow<String> = _selectedFilter.asStateFlow()
    
    init {
        loadNotifications()
    }
    
    fun loadNotifications(type: String? = null, isRead: Boolean? = null) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)
            
            try {
                notificationRepository.getNotifications(
                    page = 1,
                    perPage = 50,
                    type = type,
                    isRead = isRead
                ).collect { result ->
                    result.fold(
                        onSuccess = { paginatedData ->
                            _notifications.value = paginatedData.data
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
    
    fun filterNotifications(filter: String) {
        _selectedFilter.value = filter
        
        when (filter) {
            "all" -> loadNotifications()
            "unread" -> loadNotifications(isRead = false)
            "general" -> loadNotifications(type = "general")
            "maintenance" -> loadNotifications(type = "maintenance")
            "payment" -> loadNotifications(type = "payment")
            "emergency" -> loadNotifications(type = "emergency")
        }
    }
    
    fun markAsRead(notificationId: Int) {
        viewModelScope.launch {
            try {
                notificationRepository.markAsRead(notificationId).collect { result ->
                    result.fold(
                        onSuccess = {
                            // Update local list
                            val updatedList = _notifications.value.map { notification ->
                                if (notification.id == notificationId) {
                                    notification.copy(isRead = true)
                                } else {
                                    notification
                                }
                            }
                            _notifications.value = updatedList
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(error = error.message)
                        }
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(error = e.message)
            }
        }
    }
    
    fun markAllAsRead() {
        viewModelScope.launch {
            try {
                notificationRepository.markAllAsRead().collect { result ->
                    result.fold(
                        onSuccess = {
                            // Update all notifications as read
                            val updatedList = _notifications.value.map { notification ->
                                notification.copy(isRead = true)
                            }
                            _notifications.value = updatedList
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(error = error.message)
                        }
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(error = e.message)
            }
        }
    }
    
    fun deleteNotification(notificationId: Int) {
        viewModelScope.launch {
            try {
                notificationRepository.deleteNotification(notificationId).collect { result ->
                    result.fold(
                        onSuccess = {
                            // Remove from local list
                            val updatedList = _notifications.value.filter { it.id != notificationId }
                            _notifications.value = updatedList
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(error = error.message)
                        }
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(error = e.message)
            }
        }
    }
    
    fun refreshNotifications() {
        loadNotifications()
    }
    
    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null)
    }
}

data class NotificationsUiState(
    val isLoading: Boolean = false,
    val error: String? = null
)
