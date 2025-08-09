import apiService from './apiService';
import { 
  Apartment, 
  Resident, 
  PaginatedResponse, 
  ApiResponse 
} from '../types';

class ApartmentService {
  // Get user's apartment info
  async getMyApartment(): Promise<Apartment> {
    try {
      const response = await apiService.get<ApiResponse<Apartment>>('/apartments/my');
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thông tin căn hộ');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get apartment details by ID
  async getApartmentDetail(apartmentId: number): Promise<Apartment> {
    try {
      const response = await apiService.get<ApiResponse<Apartment>>(`/apartments/${apartmentId}`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thông tin căn hộ');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get residents of an apartment
  async getApartmentResidents(apartmentId: number): Promise<Resident[]> {
    try {
      const response = await apiService.get<ApiResponse<Resident[]>>(`/apartments/${apartmentId}/residents`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách cư dân');
      }
    } catch (error) {
      throw error;
    }
  }

  // Update apartment information (for apartment owner)
  async updateApartmentInfo(apartmentId: number, data: Partial<Apartment>): Promise<Apartment> {
    try {
      const response = await apiService.put<ApiResponse<Apartment>>(`/apartments/${apartmentId}`, data);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Cập nhật thông tin căn hộ thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Add resident to apartment
  async addResident(apartmentId: number, residentData: Partial<Resident>): Promise<Resident> {
    try {
      const response = await apiService.post<ApiResponse<Resident>>(`/apartments/${apartmentId}/residents`, residentData);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Thêm cư dân thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Update resident information
  async updateResident(residentId: number, data: Partial<Resident>): Promise<Resident> {
    try {
      const response = await apiService.put<ApiResponse<Resident>>(`/residents/${residentId}`, data);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Cập nhật thông tin cư dân thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Remove resident from apartment
  async removeResident(residentId: number): Promise<void> {
    try {
      const response = await apiService.delete<ApiResponse<any>>(`/residents/${residentId}`);
      
      if (!response.success) {
        throw new Error(response.message || 'Xóa cư dân thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get apartment utility bills history
  async getUtilityBills(apartmentId: number, page: number = 1): Promise<PaginatedResponse<any>> {
    try {
      const response = await apiService.get<ApiResponse<PaginatedResponse<any>>>(
        `/apartments/${apartmentId}/utility-bills?page=${page}&limit=10`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy lịch sử hóa đơn tiện ích');
      }
    } catch (error) {
      throw error;
    }
  }
}

// Create singleton instance
const apartmentService = new ApartmentService();

export default apartmentService;
