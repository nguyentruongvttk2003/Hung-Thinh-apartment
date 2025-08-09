import apiService from './apiService';
import { ApiResponse, PaginatedResponse } from '../types';

export interface Invoice {
  id: number;
  invoice_number: string;
  apartment_id: number;
  billing_period_start: string;
  billing_period_end: string;
  due_date: string;
  management_fee: number;
  electricity_fee: number;
  water_fee: number;
  parking_fee: number;
  other_fees: number;
  total_amount: number;
  paid_amount: number;
  status: 'pending' | 'partial' | 'paid' | 'overdue' | 'cancelled';
  notes?: string;
  created_by: number;
  created_at: string;
  updated_at: string;
  
  // Computed fields for backward compatibility
  title?: string;
  amount?: number;
  description?: string;
  dueDate?: string;
  createdAt?: string;
  updatedAt?: string;
}

class InvoiceService {
  // Get invoices with pagination
  async getInvoices(page: number = 1): Promise<PaginatedResponse<Invoice>> {
    try {
      const response = await apiService.get<ApiResponse<PaginatedResponse<Invoice>>>(
        `/invoices?page=${page}&limit=10`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách hóa đơn');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get invoice details
  async getInvoiceDetail(invoiceId: number): Promise<Invoice> {
    try {
      const response = await apiService.get<ApiResponse<Invoice>>(`/invoices/${invoiceId}`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy chi tiết hóa đơn');
      }
    } catch (error) {
      throw error;
    }
  }

  // Pay invoice
  async payInvoice(invoiceId: number, paymentData: any): Promise<void> {
    try {
      const response = await apiService.post<ApiResponse<any>>(
        `/invoices/${invoiceId}/pay`, 
        paymentData
      );
      
      if (!response.success) {
        throw new Error(response.message || 'Không thể thanh toán hóa đơn');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get payment history
  async getPaymentHistory(invoiceId: number): Promise<any[]> {
    try {
      const response = await apiService.get<ApiResponse<any[]>>(
        `/invoices/${invoiceId}/payments`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy lịch sử thanh toán');
      }
    } catch (error) {
      throw error;
    }
  }
}

export default new InvoiceService();
