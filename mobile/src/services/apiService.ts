import axios, { AxiosInstance, AxiosResponse } from 'axios';
import * as SecureStore from 'expo-secure-store';
import { API_CONFIG } from '../config/api';

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: API_CONFIG.BASE_URL,
      timeout: API_CONFIG.TIMEOUT,
      headers: API_CONFIG.DEFAULT_HEADERS,
    });

    // Log configuration on startup
    console.log('🔧 API Service Configuration:');
    console.log('  Base URL:', API_CONFIG.BASE_URL);
    console.log('  Timeout:', API_CONFIG.TIMEOUT);

    // Request interceptor to add auth token
    this.api.interceptors.request.use(
      async (config) => {
        try {
          const token = await SecureStore.getItemAsync('auth_token');
          if (token) {
            config.headers.Authorization = `Bearer ${token}`;
          }
          const fullUrl = `${config.baseURL || ''}${config.url || ''}`;
          console.log('📤 API Request:', config.method?.toUpperCase(), fullUrl);
        } catch (error) {
          console.log('⚠️ Error getting token:', error);
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor to handle errors
    this.api.interceptors.response.use(
      (response) => {
        console.log('✅ API Response:', response.status, response.config?.url || 'Unknown URL');
        return response;
      },
      async (error) => {
        // Enhanced error logging
        const status = error.response?.status || 'N/A';
        const url = error.response?.config?.url || error.config?.url || 'Unknown URL';
        const message = error.message || 'Unknown error';
        const responseData = error.response?.data || null;
        
        console.log('❌ API Error Details:');
        console.log('  Status:', status);
        console.log('  URL:', url);
        console.log('  Message:', message);
        console.log('  Error Type:', error.code || 'Unknown');
        
        if (responseData) {
          console.log('  Response Data:', responseData);
        }
        
        // Network error specific handling
        if (error.code === 'NETWORK_ERROR' || message.includes('Network Error')) {
          console.log('🌐 Network Error - Check:');
          console.log('  1. Backend server is running');
          console.log('  2. Correct IP address in config');
          console.log('  3. Firewall/antivirus settings');
          console.log('  4. Phone and computer on same network');
        }
        
        if (error.response?.status === 401) {
          // Clear stored data on unauthorized
          await SecureStore.deleteItemAsync('auth_token');
          await SecureStore.deleteItemAsync('user_data');
        }
        return Promise.reject(error);
      }
    );
  }

  async get<T>(url: string, config?: any): Promise<T> {
    const response: AxiosResponse<T> = await this.api.get(url, config);
    return response.data;
  }

  async post<T>(url: string, data?: any, config?: any): Promise<T> {
    const response: AxiosResponse<T> = await this.api.post(url, data, config);
    return response.data;
  }

  async put<T>(url: string, data?: any, config?: any): Promise<T> {
    const response: AxiosResponse<T> = await this.api.put(url, data, config);
    return response.data;
  }

  async delete<T>(url: string, config?: any): Promise<T> {
    const response: AxiosResponse<T> = await this.api.delete(url, config);
    return response.data;
  }

  async patch<T>(url: string, data?: any, config?: any): Promise<T> {
    const response: AxiosResponse<T> = await this.api.patch(url, data, config);
    return response.data;
  }
}

// Create and export singleton instance
const apiService = new ApiService();
export default apiService;
