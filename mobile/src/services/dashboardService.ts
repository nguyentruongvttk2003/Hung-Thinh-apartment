import apiService from './apiService';
import { ApiResponse } from '../types';

export interface RecentActivity {
  id: string | number;
  type: string;
  title: string;
  description: string;
  created_at: string;
  icon?: string;
  color?: string;
}

export interface DashboardStats {
  total_apartments: number;
  total_residents: number;
  pending_invoices: number;
  total_revenue: number;
  unread_notifications: number;
  active_votes: number;
  pending_feedbacks: number;
  scheduled_maintenances: number;
}

class DashboardService {
  // Get dashboard statistics
  async getDashboardStats(): Promise<DashboardStats> {
    try {
      const response = await apiService.get<ApiResponse<DashboardStats>>('/dashboard/stats');
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thống kê tổng quan');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get recent activities
  async getRecentActivities(): Promise<RecentActivity[]> {
    try {
      const response = await apiService.get<ApiResponse<RecentActivity[]>>('/dashboard/recent-activities');
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy hoạt động gần đây');
      }
    } catch (error) {
      throw error;
    }
  }

  // Test endpoint
  async testDashboard(): Promise<any> {
    try {
      const response = await apiService.get<ApiResponse<any>>('/dashboard/test');
      return response;
    } catch (error) {
      throw error;
    }
  }
}

export default new DashboardService();
