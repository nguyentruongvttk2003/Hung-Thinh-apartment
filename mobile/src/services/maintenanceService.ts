import apiService from './apiService';
import { 
  Device, 
  Maintenance, 
  PaginatedResponse, 
  ApiResponse 
} from '../types';

class MaintenanceService {
  // Get all devices
  async getDevices(page: number = 1): Promise<PaginatedResponse<Device>> {
    try {
      const response = await apiService.get<ApiResponse<PaginatedResponse<Device>>>(
        `/devices?page=${page}&limit=10`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách thiết bị');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get device details
  async getDeviceDetail(deviceId: number): Promise<Device> {
    try {
      const response = await apiService.get<ApiResponse<Device>>(`/devices/${deviceId}`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thông tin thiết bị');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get maintenance history for a device
  async getDeviceMaintenanceHistory(deviceId: number, page: number = 1): Promise<PaginatedResponse<Maintenance>> {
    try {
      const response = await apiService.get<ApiResponse<PaginatedResponse<Maintenance>>>(
        `/devices/${deviceId}/maintenances?page=${page}&limit=10`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy lịch sử bảo trì');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get all maintenance records
  async getMaintenanceRecords(page: number = 1, status?: string): Promise<PaginatedResponse<Maintenance>> {
    try {
      const params = new URLSearchParams({ page: page.toString(), limit: '10' });
      if (status) params.append('status', status);
      
      const response = await apiService.get<ApiResponse<PaginatedResponse<Maintenance>>>(
        `/maintenances?${params.toString()}`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách bảo trì');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get maintenance detail
  async getMaintenanceDetail(maintenanceId: number): Promise<Maintenance> {
    try {
      const response = await apiService.get<ApiResponse<Maintenance>>(`/maintenances/${maintenanceId}`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thông tin bảo trì');
      }
    } catch (error) {
      throw error;
    }
  }

  // Create maintenance request (for residents)
  async createMaintenanceRequest(data: {
    deviceId: number;
    title: string;
    description: string;
    type: 'routine' | 'repair' | 'emergency';
  }): Promise<Maintenance> {
    try {
      const response = await apiService.post<ApiResponse<Maintenance>>('/maintenances', data);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Tạo yêu cầu bảo trì thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Update maintenance status (for technicians)
  async updateMaintenanceStatus(
    maintenanceId: number, 
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled',
    notes?: string,
    cost?: number
  ): Promise<Maintenance> {
    try {
      const data: any = { status };
      if (notes) data.notes = notes;
      if (cost) data.cost = cost;
      if (status === 'completed') data.completedDate = new Date().toISOString();
      
      const response = await apiService.put<ApiResponse<Maintenance>>(`/maintenances/${maintenanceId}`, data);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Cập nhật trạng thái bảo trì thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get upcoming maintenance schedule
  async getUpcomingMaintenance(): Promise<Maintenance[]> {
    try {
      const response = await apiService.get<ApiResponse<Maintenance[]>>('/maintenances/upcoming?limit=5');
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy lịch bảo trì sắp tới');
      }
    } catch (error) {
      throw error;
    }
  }

  // Report device issue (residents can report problems)
  async reportDeviceIssue(data: {
    deviceId: number;
    title: string;
    description: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
  }): Promise<Maintenance> {
    try {
      const maintenanceData = {
        ...data,
        type: 'repair' as const,
      };
      
      const response = await apiService.post<ApiResponse<Maintenance>>('/maintenances/report', maintenanceData);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Báo cáo sự cố thất bại');
      }
    } catch (error) {
      throw error;
    }
  }
}

// Create singleton instance
const maintenanceService = new MaintenanceService();

export default maintenanceService;
