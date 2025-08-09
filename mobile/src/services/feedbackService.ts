import apiService from './apiService';
import { ApiResponse, PaginatedResponse } from '../types';

export interface Feedback {
  id: number;
  user_id: number;
  apartment_id?: number;
  category: 'maintenance' | 'complaint' | 'suggestion' | 'security' | 'other';
  title: string;
  description: string;
  priority: 'low' | 'normal' | 'high' | 'urgent';
  status: 'submitted' | 'reviewing' | 'assigned' | 'in_progress' | 'resolved' | 'closed';
  assigned_to?: number;
  resolution?: string;
  feedback_rating?: number;
  attachments?: string;
  resolved_at?: string;
  created_at: string;
  updated_at: string;
  
  // Relationships
  user?: {
    id: number;
    name: string;
    email: string;
  };
  apartment?: {
    id: number;
    apartment_number: string;
  };
  assignee?: {
    id: number;
    name: string;
  };
  
  // Computed fields for compatibility
  createdAt?: string;
  updatedAt?: string;
}

export interface CreateFeedbackRequest {
  apartment_id?: number;
  category: string;
  title: string;
  description: string;
  priority: string;
  attachments?: string[];
}

class FeedbackService {
  // Get feedbacks with pagination
  async getFeedbacks(page: number = 1): Promise<PaginatedResponse<Feedback>> {
    try {
      const response = await apiService.get<ApiResponse<PaginatedResponse<Feedback>>>(
        `/feedbacks?page=${page}&limit=10`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách phản hồi');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get feedback by ID
  async getFeedbackById(feedbackId: number): Promise<Feedback> {
    try {
      const response = await apiService.get<ApiResponse<Feedback>>(`/feedbacks/${feedbackId}`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thông tin phản hồi');
      }
    } catch (error) {
      throw error;
    }
  }

  // Create new feedback
  async createFeedback(feedbackData: CreateFeedbackRequest): Promise<Feedback> {
    try {
      const response = await apiService.post<ApiResponse<Feedback>>('/feedbacks', feedbackData);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể tạo phản hồi');
      }
    } catch (error) {
      throw error;
    }
  }

  // Rate feedback (after resolved)
  async rateFeedback(feedbackId: number, rating: number): Promise<void> {
    try {
      const response = await apiService.post<ApiResponse<any>>(
        `/feedbacks/${feedbackId}/rate`,
        { rating }
      );
      
      if (!response.success) {
        throw new Error(response.message || 'Không thể đánh giá phản hồi');
      }
    } catch (error) {
      throw error;
    }
  }
}

export default new FeedbackService();
