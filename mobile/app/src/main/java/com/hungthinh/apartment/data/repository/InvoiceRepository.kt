package com.hungthinh.apartment.data.repository

import com.hungthinh.apartment.data.api.ApiService
import com.hungthinh.apartment.data.model.*
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class InvoiceRepository @Inject constructor(
    private val apiService: ApiService
) {
    
    fun getInvoices(
        page: Int = 1,
        perPage: Int = 20,
        status: String? = null,
        type: String? = null,
        apartmentId: Int? = null
    ): Flow<Result<PaginatedData<Invoice>>> = flow {
        try {
            val response = apiService.getInvoices(page, perPage, status, type, apartmentId)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val data = response.body()!!.data!!
                emit(Result.success(data))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get invoices"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getInvoice(id: Int): Flow<Result<Invoice>> = flow {
        try {
            val response = apiService.getInvoice(id)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val invoice = response.body()!!.data!!
                emit(Result.success(invoice))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get invoice"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun payInvoice(id: Int, paymentRequest: PaymentRequest): Flow<Result<Invoice>> = flow {
        try {
            val response = apiService.payInvoice(id, paymentRequest)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val invoice = response.body()!!.data!!
                emit(Result.success(invoice))
            } else {
                val errorMessage = response.body()?.message ?: "Payment failed"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getInvoiceSummary(): Flow<Result<Map<String, Any>>> = flow {
        try {
            val response = apiService.getInvoiceSummary()
            
            if (response.isSuccessful && response.body()?.success == true) {
                val summary = response.body()!!.data!!
                emit(Result.success(summary))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get summary"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getPendingInvoices(): Flow<Result<List<Invoice>>> = flow {
        try {
            val response = apiService.getInvoices(status = "pending", perPage = 50)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val data = response.body()!!.data!!.data
                emit(Result.success(data))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get pending invoices"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
    
    fun getOverdueInvoices(): Flow<Result<List<Invoice>>> = flow {
        try {
            val response = apiService.getInvoices(status = "overdue", perPage = 50)
            
            if (response.isSuccessful && response.body()?.success == true) {
                val data = response.body()!!.data!!.data
                emit(Result.success(data))
            } else {
                val errorMessage = response.body()?.message ?: "Failed to get overdue invoices"
                emit(Result.failure(Exception(errorMessage)))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }
}
