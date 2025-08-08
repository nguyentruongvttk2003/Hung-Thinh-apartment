package com.hungthinh.apartment.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.hungthinh.apartment.data.model.*
import com.hungthinh.apartment.data.repository.InvoiceRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class InvoicesViewModel @Inject constructor(
    private val invoiceRepository: InvoiceRepository
) : ViewModel() {
    
    private val _uiState = MutableStateFlow(InvoicesUiState())
    val uiState: StateFlow<InvoicesUiState> = _uiState.asStateFlow()
    
    private val _invoices = MutableStateFlow<List<Invoice>>(emptyList())
    val invoices: StateFlow<List<Invoice>> = _invoices.asStateFlow()
    
    private val _selectedFilter = MutableStateFlow("all")
    val selectedFilter: StateFlow<String> = _selectedFilter.asStateFlow()
    
    private val _invoiceSummary = MutableStateFlow<Map<String, Any>?>(null)
    val invoiceSummary: StateFlow<Map<String, Any>?> = _invoiceSummary.asStateFlow()
    
    init {
        loadInvoices()
        loadInvoiceSummary()
    }
    
    fun loadInvoices(status: String? = null, type: String? = null) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)
            
            try {
                invoiceRepository.getInvoices(
                    page = 1,
                    perPage = 50,
                    status = status,
                    type = type
                ).collect { result ->
                    result.fold(
                        onSuccess = { paginatedData ->
                            _invoices.value = paginatedData.data
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
    
    private fun loadInvoiceSummary() {
        viewModelScope.launch {
            try {
                invoiceRepository.getInvoiceSummary().collect { result ->
                    result.fold(
                        onSuccess = { summary ->
                            _invoiceSummary.value = summary
                        },
                        onFailure = { error ->
                            // Ignore summary load errors for now
                        }
                    )
                }
            } catch (e: Exception) {
                // Ignore summary load errors for now
            }
        }
    }
    
    fun filterInvoices(filter: String) {
        _selectedFilter.value = filter
        
        when (filter) {
            "all" -> loadInvoices()
            "pending" -> loadInvoices(status = "pending")
            "paid" -> loadInvoices(status = "paid")
            "overdue" -> loadInvoices(status = "overdue")
            "monthly_fee" -> loadInvoices(type = "monthly_fee")
            "maintenance" -> loadInvoices(type = "maintenance")
            "service" -> loadInvoices(type = "service")
        }
    }
    
    fun payInvoice(invoiceId: Int, paymentMethod: String, paymentReference: String?, notes: String?) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isPaymentLoading = true)
            
            try {
                val paymentRequest = PaymentRequest(
                    invoiceId = invoiceId,
                    paymentMethod = paymentMethod,
                    paymentReference = paymentReference,
                    notes = notes
                )
                
                invoiceRepository.payInvoice(invoiceId, paymentRequest).collect { result ->
                    result.fold(
                        onSuccess = { updatedInvoice ->
                            // Update local list
                            val updatedList = _invoices.value.map { invoice ->
                                if (invoice.id == invoiceId) {
                                    updatedInvoice
                                } else {
                                    invoice
                                }
                            }
                            _invoices.value = updatedList
                            _uiState.value = _uiState.value.copy(
                                isPaymentLoading = false,
                                paymentSuccess = true
                            )
                            
                            // Reload summary after payment
                            loadInvoiceSummary()
                        },
                        onFailure = { error ->
                            _uiState.value = _uiState.value.copy(
                                error = error.message,
                                isPaymentLoading = false
                            )
                        }
                    )
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    error = e.message,
                    isPaymentLoading = false
                )
            }
        }
    }
    
    fun getInvoiceDetails(invoiceId: Int) {
        viewModelScope.launch {
            try {
                invoiceRepository.getInvoice(invoiceId).collect { result ->
                    result.fold(
                        onSuccess = { invoice ->
                            // Update the invoice in the list if it exists
                            val updatedList = _invoices.value.map { existingInvoice ->
                                if (existingInvoice.id == invoiceId) {
                                    invoice
                                } else {
                                    existingInvoice
                                }
                            }
                            _invoices.value = updatedList
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
    
    fun refreshInvoices() {
        loadInvoices()
        loadInvoiceSummary()
    }
    
    fun clearError() {
        _uiState.value = _uiState.value.copy(error = null)
    }
    
    fun clearPaymentSuccess() {
        _uiState.value = _uiState.value.copy(paymentSuccess = false)
    }
}

data class InvoicesUiState(
    val isLoading: Boolean = false,
    val isPaymentLoading: Boolean = false,
    val error: String? = null,
    val paymentSuccess: Boolean = false
)
