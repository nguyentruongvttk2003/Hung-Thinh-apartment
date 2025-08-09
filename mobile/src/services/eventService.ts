import apiService from './apiService';
import { ApiResponse, PaginatedResponse } from '../types';

export interface Event {
  id: number;
  title: string;
  description: string;
  type: 'meeting' | 'maintenance' | 'power_outage' | 'water_outage' | 'social_event' | 'emergency';
  scope: 'all' | 'block' | 'floor' | 'apartment' | 'specific';
  target_scope?: any;
  start_time: string;
  end_time?: string;
  location?: string;
  status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
  created_by: number;
  notes?: string;
  created_at: string;
  updated_at: string;
  
  // Computed fields for compatibility
  startTime?: string;
  endTime?: string;
  createdAt?: string;
  updatedAt?: string;
}

class EventService {
  // Get events with pagination
  async getEvents(page: number = 1): Promise<PaginatedResponse<Event>> {
    try {
      const response = await apiService.get<ApiResponse<PaginatedResponse<Event>>>(
        `/events?page=${page}&limit=10`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách sự kiện');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get upcoming events
  async getUpcomingEvents(): Promise<Event[]> {
    try {
      const response = await apiService.get<ApiResponse<Event[]>>('/events/upcoming');
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách sự kiện sắp tới');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get event by ID
  async getEventById(eventId: number): Promise<Event> {
    try {
      const response = await apiService.get<ApiResponse<Event>>(`/events/${eventId}`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thông tin sự kiện');
      }
    } catch (error) {
      throw error;
    }
  }
}

export default new EventService();
