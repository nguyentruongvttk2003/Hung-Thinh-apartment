import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import invoiceService, { Invoice } from '../services/invoiceService';

interface InvoiceState {
  invoices: Invoice[];
  selectedInvoice: Invoice | null;
  isLoading: boolean;
  error: string | null;
  page: number;
  hasMore: boolean;
}

const initialState: InvoiceState = {
  invoices: [],
  selectedInvoice: null,
  isLoading: false,
  error: null,
  page: 1,
  hasMore: true,
};

// Async thunks
export const fetchInvoices = createAsyncThunk(
  'invoice/fetch',
  async ({ page = 1, refresh = false }: { page?: number; refresh?: boolean }, { rejectWithValue }) => {
    try {
      const response = await invoiceService.getInvoices(page);
      return { ...response, refresh };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải hóa đơn');
    }
  }
);

export const fetchInvoiceDetail = createAsyncThunk(
  'invoice/fetchDetail',
  async (invoiceId: number, { rejectWithValue }) => {
    try {
      const response = await invoiceService.getInvoiceDetail(invoiceId);
      return response;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải chi tiết hóa đơn');
    }
  }
);

export const payInvoice = createAsyncThunk(
  'invoice/pay',
  async ({ invoiceId, paymentData }: { invoiceId: number; paymentData: any }, { rejectWithValue }) => {
    try {
      await invoiceService.payInvoice(invoiceId, paymentData);
      return invoiceId;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi thanh toán hóa đơn');
    }
  }
);

const invoiceSlice = createSlice({
  name: 'invoice',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    resetInvoices: (state) => {
      state.invoices = [];
      state.page = 1;
      state.hasMore = true;
    },
    clearSelectedInvoice: (state) => {
      state.selectedInvoice = null;
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch invoices
      .addCase(fetchInvoices.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchInvoices.fulfilled, (state, action) => {
        state.isLoading = false;
        const { data, currentPage, totalPages, refresh } = action.payload;
        
        if (refresh || currentPage === 1) {
          state.invoices = data;
        } else {
          state.invoices = [...state.invoices, ...data];
        }
        
        state.page = currentPage || 1;
        state.hasMore = (currentPage || 1) < (totalPages || 1);
      })
      .addCase(fetchInvoices.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch invoice detail
      .addCase(fetchInvoiceDetail.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchInvoiceDetail.fulfilled, (state, action) => {
        state.isLoading = false;
        state.selectedInvoice = action.payload;
      })
      .addCase(fetchInvoiceDetail.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Pay invoice
      .addCase(payInvoice.fulfilled, (state, action) => {
        const invoiceId = action.payload;
        const invoice = state.invoices.find(inv => inv.id === invoiceId);
        if (invoice) {
          invoice.status = 'paid';
        }
        if (state.selectedInvoice && state.selectedInvoice.id === invoiceId) {
          state.selectedInvoice.status = 'paid';
        }
      });
  },
});

export const { clearError, resetInvoices, clearSelectedInvoice } = invoiceSlice.actions;
export default invoiceSlice.reducer;
