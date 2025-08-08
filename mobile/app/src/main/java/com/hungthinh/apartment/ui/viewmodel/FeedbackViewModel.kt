package com.hungthinh.apartment.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.hungthinh.apartment.data.model.*
import com.hungthinh.apartment.data.repository.FeedbackRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class FeedbackViewModel @Inject constructor(
    private val feedbackRepository: FeedbackRepository
) : ViewModel() {
    
    private val _uiState = MutableStateFlow(FeedbackUiState())
    val uiState: StateFlow<FeedbackUiState> = _uiState.asStateFlow()
    
    private val _feedbackList = MutableStateFlow<List<Feedback>>(emptyList())
    val feedbackList: StateFlow<List<Feedback>> = _feedbackList.asStateFlow()
    
    private val _selectedFilter = MutableStateFlow("all")
    val selectedFilter: StateFlow<String> = _selectedFilter.asStateFlow()
    
    init {
        loadFeedback()
    }
    
    fun loadFeedback(category: String? = null, status: String? = null, priority: String? = null) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)
            
            try {
                feedbackRepository.getFeedback(
                    page = 1,
                    perPage = 50,
                    category = category,
                    status = status,
                    priority = priority
                ).collect { result ->
                    result.fold(
                        onSuccess = { paginatedData ->
                            _feedbackList.value = paginatedData.data
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
    
    fun createFeedback(
        category: String,
        priority: String,
        title: String,
        description: String,
        location: String?,
        contactPreferred: String?,
        urgencyLevel: Int
    ) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isSubmittingFeedback = true)
            
            try {
                val feedbackRequest = FeedbackRequest(
                    category = category,
                    priority = priority,
                    title = title,
                    description = description,
                    location = location,
                    contactPreferred = contactPreferred,
                    urgencyLevel = urgencyLevel
                )
                
                feedbackRepository.createFeedback(feedbackRequest).collect { result ->
                    result.fold(
                        onSuccess = { newFeedback ->
                            // Add to local list
                            val updatedList = listOf(newFeedback) + _feedbackList.value
                            _feedbackList.value = updatedList
                            _uiState.value = _uiState.value.copy(
                                isSubmittingFeedback = false,
                                feedbackSubmitted = true
                            )
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(
                                error = error.message,
                                isSubmittingFeedback = false
                            )
                        }
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    error = e.message,
                    isSubmittingFeedback = false
                )
            }
        }
    }
    
    fun updateFeedback(
        feedbackId: Int,
        category: String,
        priority: String,
        title: String,
        description: String,
        location: String?,
        contactPreferred: String?,
        urgencyLevel: Int
    ) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isSubmittingFeedback = true)
            
            try {
                val feedbackRequest = FeedbackRequest(
                    category = category,
                    priority = priority,
                    title = title,
                    description = description,
                    location = location,
                    contactPreferred = contactPreferred,
                    urgencyLevel = urgencyLevel
                )
                
                feedbackRepository.updateFeedback(feedbackId, feedbackRequest).collect { result ->
                    result.fold(
                        onSuccess = { updatedFeedback ->
                            // Update local list
                            val updatedList = _feedbackList.value.map { feedback ->
                                if (feedback.id == feedbackId) {
                                    updatedFeedback
                                } else {
                                    feedback
                                }
                            }
                            _feedbackList.value = updatedList
                            _uiState.value = _uiState.value.copy(
                                isSubmittingFeedback = false,
                                feedbackSubmitted = true
                            )
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(
                                error = error.message,
                                isSubmittingFeedback = false
                            )
                        }
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    error = e.message,
                    isSubmittingFeedback = false
                )
            }
        }
    }
    
    fun filterFeedback(filter: String) {
        _selectedFilter.value = filter
        
        when (filter) {
            "all" -> loadFeedback()
            "maintenance" -> loadFeedback(category = "maintenance")
            "complaint" -> loadFeedback(category = "complaint")
            "suggestion" -> loadFeedback(category = "suggestion")
            "submitted" -> loadFeedback(status = "submitted")
            "in_progress" -> loadFeedback(status = "in_progress")
            "resolved" -> loadFeedback(status = "resolved")
            "high" -> loadFeedback(priority = "high")
            "urgent" -> loadFeedback(priority = "urgent")
        }
    }
    
    fun deleteFeedback(feedbackId: Int) {
        viewModelScope.launch {
            try {
                feedbackRepository.deleteFeedback(feedbackId).collect { result ->
                    result.fold(
                        onSuccess = {
                            // Remove from local list
                            val updatedList = _feedbackList.value.filter { it.id != feedbackId }
                            _feedbackList.value = updatedList
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
    
    fun rateFeedback(feedbackId: Int, rating: Int) {
        viewModelScope.launch {
            try {
                feedbackRepository.rateFeedback(feedbackId, rating).collect { result ->
                    result.fold(
                        onSuccess = {
                            // Update local list - reload from server instead of local update
                            loadFeedback()
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
    
    fun refreshFeedback() {
        loadFeedback()
    }
    
    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null)
    }
    
    fun clearFeedbackSubmitted() {
        _uiState.value = _uiState.value.copy(feedbackSubmitted = false)
    }
}

data class FeedbackUiState(
    val isLoading: Boolean = false,
    val isSubmittingFeedback: Boolean = false,
    val error: String? = null,
    val feedbackSubmitted: Boolean = false
)
