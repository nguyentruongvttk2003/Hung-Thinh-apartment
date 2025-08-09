import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { Device, Maintenance, PaginatedResponse, ApiResponse } from '../types';
import maintenanceService from '../services/maintenanceService';

interface MaintenanceState {
  devices: Device[];
  maintenances: Maintenance[];
  upcomingMaintenance: Maintenance[];
  selectedDevice: Device | null;
  selectedMaintenance: Maintenance | null;
  isLoading: boolean;
  error: string | null;
  page: number;
  hasMore: boolean;
}

const initialState: MaintenanceState = {
  devices: [],
  maintenances: [],
  upcomingMaintenance: [],
  selectedDevice: null,
  selectedMaintenance: null,
  isLoading: false,
  error: null,
  page: 1,
  hasMore: true,
};

// Async thunks
export const fetchDevices = createAsyncThunk(
  'maintenance/fetchDevices',
  async ({ page = 1, refresh = false }: { page?: number; refresh?: boolean }, { rejectWithValue }) => {
    try {
      const response = await maintenanceService.getDevices(page);
      return { ...response, refresh };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải danh sách thiết bị');
    }
  }
);

export const fetchDeviceDetail = createAsyncThunk(
  'maintenance/fetchDeviceDetail',
  async (deviceId: number, { rejectWithValue }) => {
    try {
      const device = await maintenanceService.getDeviceDetail(deviceId);
      return device;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải thông tin thiết bị');
    }
  }
);

export const fetchMaintenanceRecords = createAsyncThunk(
  'maintenance/fetchRecords',
  async ({ page = 1, refresh = false, status }: { page?: number; refresh?: boolean; status?: string }, { rejectWithValue }) => {
    try {
      const response = await maintenanceService.getMaintenanceRecords(page, status);
      return { ...response, refresh };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải danh sách bảo trì');
    }
  }
);

export const fetchMaintenanceDetail = createAsyncThunk(
  'maintenance/fetchDetail',
  async (maintenanceId: number, { rejectWithValue }) => {
    try {
      const maintenance = await maintenanceService.getMaintenanceDetail(maintenanceId);
      return maintenance;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải thông tin bảo trì');
    }
  }
);

export const createMaintenanceRequest = createAsyncThunk(
  'maintenance/createRequest',
  async (data: {
    deviceId: number;
    title: string;
    description: string;
    type: 'routine' | 'repair' | 'emergency';
  }, { rejectWithValue }) => {
    try {
      const maintenance = await maintenanceService.createMaintenanceRequest(data);
      return maintenance;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Tạo yêu cầu bảo trì thất bại');
    }
  }
);

export const reportDeviceIssue = createAsyncThunk(
  'maintenance/reportIssue',
  async (data: {
    deviceId: number;
    title: string;
    description: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
  }, { rejectWithValue }) => {
    try {
      const maintenance = await maintenanceService.reportDeviceIssue(data);
      return maintenance;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Báo cáo sự cố thất bại');
    }
  }
);

export const fetchUpcomingMaintenance = createAsyncThunk(
  'maintenance/fetchUpcoming',
  async (_, { rejectWithValue }) => {
    try {
      const maintenance = await maintenanceService.getUpcomingMaintenance();
      return maintenance;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải lịch bảo trì sắp tới');
    }
  }
);

export const updateMaintenanceStatus = createAsyncThunk(
  'maintenance/updateStatus',
  async ({ 
    maintenanceId, 
    status, 
    notes, 
    cost 
  }: { 
    maintenanceId: number; 
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
    notes?: string;
    cost?: number;
  }, { rejectWithValue }) => {
    try {
      const maintenance = await maintenanceService.updateMaintenanceStatus(maintenanceId, status, notes, cost);
      return maintenance;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Cập nhật trạng thái bảo trì thất bại');
    }
  }
);

const maintenanceSlice = createSlice({
  name: 'maintenance',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    resetDevices: (state) => {
      state.devices = [];
      state.page = 1;
      state.hasMore = true;
    },
    resetMaintenances: (state) => {
      state.maintenances = [];
      state.page = 1;
      state.hasMore = true;
    },
    setSelectedDevice: (state, action: PayloadAction<Device | null>) => {
      state.selectedDevice = action.payload;
    },
    setSelectedMaintenance: (state, action: PayloadAction<Maintenance | null>) => {
      state.selectedMaintenance = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch devices cases
      .addCase(fetchDevices.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchDevices.fulfilled, (state, action) => {
        state.isLoading = false;
        const { data, last_page, current_page, refresh } = action.payload;
        
        if (refresh || current_page === 1) {
          state.devices = data;
        } else {
          state.devices.push(...data);
        }
        
        state.page = current_page;
        state.hasMore = current_page < last_page;
        state.error = null;
      })
      .addCase(fetchDevices.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch device detail cases
      .addCase(fetchDeviceDetail.fulfilled, (state, action) => {
        state.selectedDevice = action.payload;
      })
      
      // Fetch maintenance records cases
      .addCase(fetchMaintenanceRecords.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchMaintenanceRecords.fulfilled, (state, action) => {
        state.isLoading = false;
        const { data, last_page, current_page, refresh } = action.payload;
        
        if (refresh || current_page === 1) {
          state.maintenances = data;
        } else {
          state.maintenances.push(...data);
        }
        
        state.page = current_page;
        state.hasMore = current_page < last_page;
        state.error = null;
      })
      .addCase(fetchMaintenanceRecords.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch maintenance detail cases
      .addCase(fetchMaintenanceDetail.fulfilled, (state, action) => {
        state.selectedMaintenance = action.payload;
      })
      
      // Create maintenance request cases
      .addCase(createMaintenanceRequest.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(createMaintenanceRequest.fulfilled, (state, action) => {
        state.isLoading = false;
        state.maintenances.unshift(action.payload);
        state.error = null;
      })
      .addCase(createMaintenanceRequest.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Report device issue cases
      .addCase(reportDeviceIssue.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(reportDeviceIssue.fulfilled, (state, action) => {
        state.isLoading = false;
        state.maintenances.unshift(action.payload);
        state.error = null;
      })
      .addCase(reportDeviceIssue.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch upcoming maintenance cases
      .addCase(fetchUpcomingMaintenance.fulfilled, (state, action) => {
        state.upcomingMaintenance = action.payload;
      })
      
      // Update maintenance status cases
      .addCase(updateMaintenanceStatus.fulfilled, (state, action) => {
        const updatedMaintenance = action.payload;
        const index = state.maintenances.findIndex(m => m.id === updatedMaintenance.id);
        if (index !== -1) {
          state.maintenances[index] = updatedMaintenance;
        }
        if (state.selectedMaintenance?.id === updatedMaintenance.id) {
          state.selectedMaintenance = updatedMaintenance;
        }
      });
  },
});

export const { 
  clearError, 
  resetDevices, 
  resetMaintenances, 
  setSelectedDevice, 
  setSelectedMaintenance 
} = maintenanceSlice.actions;
export default maintenanceSlice.reducer;
