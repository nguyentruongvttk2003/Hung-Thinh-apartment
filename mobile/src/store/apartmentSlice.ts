import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { Apartment, Resident, ApiResponse } from '../types';
import apartmentService from '../services/apartmentService';

interface ApartmentState {
  apartment: Apartment | null;
  residents: Resident[];
  isLoading: boolean;
  error: string | null;
}

const initialState: ApartmentState = {
  apartment: null,
  residents: [],
  isLoading: false,
  error: null,
};

// Async thunks
export const fetchMyApartment = createAsyncThunk(
  'apartment/fetchMy',
  async (_, { rejectWithValue }) => {
    try {
      const apartment = await apartmentService.getMyApartment();
      return apartment;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Không thể tải thông tin căn hộ');
    }
  }
);

export const fetchApartmentResidents = createAsyncThunk(
  'apartment/fetchResidents',
  async (apartmentId: number, { rejectWithValue }) => {
    try {
      const residents = await apartmentService.getApartmentResidents(apartmentId);
      return residents;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Không thể tải danh sách cư dân');
    }
  }
);

export const updateApartmentInfo = createAsyncThunk(
  'apartment/updateInfo',
  async ({ apartmentId, data }: { apartmentId: number; data: Partial<Apartment> }, { rejectWithValue }) => {
    try {
      const apartment = await apartmentService.updateApartmentInfo(apartmentId, data);
      return apartment;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Cập nhật thông tin căn hộ thất bại');
    }
  }
);

export const addResident = createAsyncThunk(
  'apartment/addResident',
  async ({ apartmentId, residentData }: { apartmentId: number; residentData: Partial<Resident> }, { rejectWithValue }) => {
    try {
      const resident = await apartmentService.addResident(apartmentId, residentData);
      return resident;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Thêm cư dân thất bại');
    }
  }
);

export const updateResident = createAsyncThunk(
  'apartment/updateResident',
  async ({ residentId, data }: { residentId: number; data: Partial<Resident> }, { rejectWithValue }) => {
    try {
      const resident = await apartmentService.updateResident(residentId, data);
      return resident;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Cập nhật thông tin cư dân thất bại');
    }
  }
);

export const removeResident = createAsyncThunk(
  'apartment/removeResident',
  async (residentId: number, { rejectWithValue }) => {
    try {
      await apartmentService.removeResident(residentId);
      return residentId;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Xóa cư dân thất bại');
    }
  }
);

const apartmentSlice = createSlice({
  name: 'apartment',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    clearApartment: (state) => {
      state.apartment = null;
      state.residents = [];
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch apartment cases
      .addCase(fetchMyApartment.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchMyApartment.fulfilled, (state, action) => {
        state.isLoading = false;
        state.apartment = action.payload;
        state.error = null;
      })
      .addCase(fetchMyApartment.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch residents cases
      .addCase(fetchApartmentResidents.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchApartmentResidents.fulfilled, (state, action) => {
        state.isLoading = false;
        state.residents = action.payload;
        state.error = null;
      })
      .addCase(fetchApartmentResidents.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Update apartment cases
      .addCase(updateApartmentInfo.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(updateApartmentInfo.fulfilled, (state, action) => {
        state.isLoading = false;
        state.apartment = action.payload;
        state.error = null;
      })
      .addCase(updateApartmentInfo.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Add resident cases
      .addCase(addResident.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(addResident.fulfilled, (state, action) => {
        state.isLoading = false;
        state.residents.push(action.payload);
        state.error = null;
      })
      .addCase(addResident.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Update resident cases
      .addCase(updateResident.fulfilled, (state, action) => {
        const index = state.residents.findIndex(r => r.id === action.payload.id);
        if (index !== -1) {
          state.residents[index] = action.payload;
        }
      })
      
      // Remove resident cases
      .addCase(removeResident.fulfilled, (state, action) => {
        state.residents = state.residents.filter(r => r.id !== action.payload);
      });
  },
});

export const { clearError, clearApartment } = apartmentSlice.actions;
export default apartmentSlice.reducer;
