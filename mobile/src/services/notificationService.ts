import apiService from './apiService';
import { ApiResponse, PaginatedResponse } from '../types';

export interface Notification {
  id: number;
  title: string;
  content: string;
  type: string;
  isRead: boolean;
  createdAt: string;
  updatedAt: string;
}

class NotificationService {
  // Get notifications with pagination
  async getNotifications(page: number = 1): Promise<PaginatedResponse<Notification>> {
    try {
      const response = await apiService.get<ApiResponse<PaginatedResponse<Notification>>>(
        `/notifications?page=${page}&limit=10`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách thông báo');
      }
    } catch (error) {
      throw error;
    }
  }

  // Mark notification as read
  async markAsRead(notificationId: number): Promise<void> {
    try {
      const response = await apiService.put<ApiResponse<any>>(
        `/notifications/${notificationId}/read`
      );
      
      if (!response.success) {
        throw new Error(response.message || 'Không thể đánh dấu thông báo đã đọc');
      }
    } catch (error) {
      throw error;
    }
  }

  // Mark all notifications as read
  async markAllAsRead(): Promise<void> {
    try {
      const response = await apiService.put<ApiResponse<any>>('/notifications/mark-all-read');
      
      if (!response.success) {
        throw new Error(response.message || 'Không thể đánh dấu tất cả thông báo đã đọc');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get unread count
  async getUnreadCount(): Promise<number> {
    try {
      const response = await apiService.get<ApiResponse<{ count: number }>>('/notifications/unread-count');
      
      if (response.success && response.data) {
        return response.data.count;
      } else {
        throw new Error(response.message || 'Không thể lấy số lượng thông báo chưa đọc');
      }
    } catch (error) {
      throw error;
    }
  }
}

export default new NotificationService();
