import apiService from './apiService';
import * as SecureStore from 'expo-secure-store';
import { 
  LoginCredentials, 
  AuthResponse, 
  User,
  ApiResponse 
} from '../types';

// Login user
export const login = async (credentials: LoginCredentials): Promise<AuthResponse> => {
  try {
    const response = await apiService.post<ApiResponse<AuthResponse>>('/auth/login', credentials);
    
    if (response.success && response.data) {
      // Store token and user data securely
      await SecureStore.setItemAsync('auth_token', response.data.token);
      await SecureStore.setItemAsync('user_data', JSON.stringify(response.data.user));
      
      return response.data;
    } else {
      throw new Error(response.message || 'Đăng nhập thất bại');
    }
  } catch (error) {
    throw error;
  }
};

// Logout user
export const logout = async (): Promise<void> => {
  try {
    // Call logout API to invalidate token on server
    await apiService.post('/auth/logout');
  } catch (error) {
    // Continue with local logout even if API call fails
    console.warn('Logout API call failed:', error);
  } finally {
    // Clear stored data
    await SecureStore.deleteItemAsync('auth_token');
    await SecureStore.deleteItemAsync('user_data');
  }
};

// Get current user profile
export const getProfile = async (): Promise<User> => {
  try {
    const response = await apiService.get<ApiResponse<User>>('/auth/profile');
    
    if (response.success && response.data) {
      // Update stored user data
      await SecureStore.setItemAsync('user_data', JSON.stringify(response.data));
      return response.data;
    } else {
      throw new Error(response.message || 'Không thể lấy thông tin người dùng');
    }
  } catch (error) {
    throw error;
  }
};

// Update user profile
export const updateProfile = async (userData: Partial<User>): Promise<User> => {
  try {
    const response = await apiService.put<ApiResponse<User>>('/auth/profile', userData);
    
    if (response.success && response.data) {
      // Update stored user data
      await SecureStore.setItemAsync('user_data', JSON.stringify(response.data));
      return response.data;
    } else {
      throw new Error(response.message || 'Cập nhật thông tin thất bại');
    }
  } catch (error) {
    throw error;
  }
};

// Change password
export const changePassword = async (currentPassword: string, newPassword: string): Promise<void> => {
  try {
    const response = await apiService.put<ApiResponse<any>>('/auth/change-password', {
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: newPassword
    });
    
    if (!response.success) {
      throw new Error(response.message || 'Đổi mật khẩu thất bại');
    }
  } catch (error) {
    throw error;
  }
};

// Refresh token
export const refreshToken = async (): Promise<string> => {
  try {
    const response = await apiService.post<ApiResponse<{ token: string }>>('/auth/refresh');
    
    if (response.success && response.data) {
      // Update stored token
      await SecureStore.setItemAsync('auth_token', response.data.token);
      return response.data.token;
    } else {
      throw new Error(response.message || 'Làm mới token thất bại');
    }
  } catch (error) {
    throw error;
  }
};

// Check if user is authenticated
export const isAuthenticated = async (): Promise<boolean> => {
  try {
    const token = await SecureStore.getItemAsync('auth_token');
    return !!token;
  } catch (error) {
    return false;
  }
};

// Get stored user data
export const getStoredUser = async (): Promise<User | null> => {
  try {
    const userData = await SecureStore.getItemAsync('user_data');
    if (userData) {
      return JSON.parse(userData);
    }
    return null;
  } catch (error) {
    return null;
  }
};

// Get stored token
export const getStoredToken = async (): Promise<string | null> => {
  try {
    return await SecureStore.getItemAsync('auth_token');
  } catch (error) {
    return null;
  }
};

// Default export as an object containing all functions
const authService = {
  login,
  logout,
  getProfile,
  updateProfile,
  changePassword,
  refreshToken,
  isAuthenticated,
  getStoredUser,
  getStoredToken
};

export default authService;
