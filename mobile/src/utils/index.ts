import * as SecureStore from 'expo-secure-store';
import { Alert } from 'react-native';

// Storage utilities
export const storage = {
  async set(key: string, value: string): Promise<void> {
    try {
      await SecureStore.setItemAsync(key, value);
    } catch (error) {
      console.error('Error storing data:', error);
    }
  },

  async get(key: string): Promise<string | null> {
    try {
      return await SecureStore.getItemAsync(key);
    } catch (error) {
      console.error('Error retrieving data:', error);
      return null;
    }
  },

  async remove(key: string): Promise<void> {
    try {
      await SecureStore.deleteItemAsync(key);
    } catch (error) {
      console.error('Error removing data:', error);
    }
  },

  async clear(): Promise<void> {
    try {
      const keys = ['token', 'refreshToken', 'user'];
      await Promise.all(keys.map(key => SecureStore.deleteItemAsync(key)));
    } catch (error) {
      console.error('Error clearing storage:', error);
    }
  },
};

// Date utilities
export const dateUtils = {
  formatDate(date: string | Date): string {
    const d = new Date(date);
    return d.toLocaleDateString('vi-VN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
    });
  },

  formatDateTime(date: string | Date): string {
    const d = new Date(date);
    return d.toLocaleString('vi-VN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  },

  formatTime(date: string | Date): string {
    const d = new Date(date);
    return d.toLocaleTimeString('vi-VN', {
      hour: '2-digit',
      minute: '2-digit',
    });
  },

  isDateExpired(date: string | Date): boolean {
    return new Date(date) < new Date();
  },

  getDaysFromNow(date: string | Date): number {
    if (!date) return 0;
    
    try {
      const targetDate = new Date(date);
      const today = new Date();
      
      // Check if date is valid
      if (isNaN(targetDate.getTime())) return 0;
      
      const diffTime = targetDate.getTime() - today.getTime();
      const days = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      return days;
    } catch (error) {
      console.error('Error calculating days from now:', error);
      return 0;
    }
  },
};

// Number utilities
export const numberUtils = {
  formatCurrency(amount: number): string {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
    }).format(amount);
  },

  formatNumber(num: number): string {
    return new Intl.NumberFormat('vi-VN').format(num);
  },

  parseCurrency(currencyString: string): number {
    return parseInt(currencyString.replace(/[^\d]/g, ''), 10) || 0;
  },
};

// Validation utilities
export const validation = {
  isEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  },

  isPhoneNumber(phone: string): boolean {
    const phoneRegex = /^[0-9]{10,11}$/;
    return phoneRegex.test(phone.replace(/\s+/g, ''));
  },

  isPassword(password: string): boolean {
    return password.length >= 6;
  },

  isEmpty(value: string | null | undefined): boolean {
    return !value || value.trim().length === 0;
  },
};

// Alert utilities
export const alertUtils = {
  success(message: string): void {
    Alert.alert('Thành công', message, [{ text: 'OK' }]);
  },

  error(message: string): void {
    Alert.alert('Lỗi', message, [{ text: 'OK' }]);
  },

  confirm(
    title: string,
    message: string,
    onConfirm: () => void,
    onCancel?: () => void
  ): void {
    Alert.alert(
      title,
      message,
      [
        {
          text: 'Hủy',
          style: 'cancel',
          onPress: onCancel,
        },
        {
          text: 'Xác nhận',
          onPress: onConfirm,
        },
      ],
      { cancelable: false }
    );
  },

  info(title: string, message: string): void {
    Alert.alert(title, message, [{ text: 'OK' }]);
  },
};

// Status utilities
export const statusUtils = {
  getNotificationTypeColor(type: string): string {
    switch (type) {
      case 'info':
        return '#007AFF';
      case 'warning':
        return '#FF9500';
      case 'error':
        return '#FF3B30';
      case 'success':
        return '#34C759';
      default:
        return '#8E8E93';
    }
  },

  getInvoiceStatusColor(status: string): string {
    switch (status) {
      case 'paid':
        return '#34C759';
      case 'pending':
        return '#FF9500';
      case 'overdue':
        return '#FF3B30';
      case 'cancelled':
        return '#8E8E93';
      default:
        return '#007AFF';
    }
  },

  getInvoiceStatusText(status: string): string {
    switch (status) {
      case 'paid':
        return 'Đã thanh toán';
      case 'pending':
        return 'Chờ thanh toán';
      case 'overdue':
        return 'Quá hạn';
      case 'cancelled':
        return 'Đã hủy';
      default:
        return 'Không xác định';
    }
  },

  getMaintenanceStatusColor(status: string): string {
    switch (status) {
      case 'pending':
        return '#FF9500';
      case 'in_progress':
        return '#007AFF';
      case 'completed':
        return '#34C759';
      case 'cancelled':
        return '#8E8E93';
      default:
        return '#8E8E93';
    }
  },

  getMaintenanceStatusText(status: string): string {
    switch (status) {
      case 'pending':
        return 'Chờ xử lý';
      case 'in_progress':
        return 'Đang xử lý';
      case 'completed':
        return 'Hoàn thành';
      case 'cancelled':
        return 'Đã hủy';
      default:
        return 'Không xác định';
    }
  },

  getVoteStatusColor(status: string): string {
    switch (status) {
      case 'active':
        return '#34C759';
      case 'upcoming':
        return '#007AFF';
      case 'ended':
        return '#8E8E93';
      default:
        return '#8E8E93';
    }
  },

  getVoteStatusText(status: string): string {
    switch (status) {
      case 'active':
        return 'Đang diễn ra';
      case 'upcoming':
        return 'Sắp tới';
      case 'ended':
        return 'Đã kết thúc';
      default:
        return 'Không xác định';
    }
  },
};

// Error utilities
export const errorUtils = {
  getErrorMessage(error: any): string {
    if (typeof error === 'string') {
      return error;
    }
    
    if (error?.response?.data?.message) {
      return error.response.data.message;
    }
    
    if (error?.message) {
      return error.message;
    }
    
    return 'Đã xảy ra lỗi không xác định';
  },

  isNetworkError(error: any): boolean {
    return error?.code === 'NETWORK_ERROR' || !error?.response;
  },

  isAuthError(error: any): boolean {
    return error?.response?.status === 401;
  },
};

// File utilities
export const fileUtils = {
  getFileExtension(filename: string): string {
    return filename.split('.').pop()?.toLowerCase() || '';
  },

  isImageFile(filename: string): boolean {
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    return imageExtensions.includes(this.getFileExtension(filename));
  },

  formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  },
};

// Device utilities
export const deviceUtils = {
  getDeviceIcon(type: string): string {
    switch (type.toLowerCase()) {
      case 'elevator':
        return 'elevator';
      case 'generator':
        return 'flash';
      case 'pump':
        return 'water-pump';
      case 'security':
        return 'security';
      case 'fire_safety':
        return 'fire';
      case 'lighting':
        return 'lightbulb';
      case 'hvac':
        return 'air-conditioner';
      default:
        return 'device-hub';
    }
  },

  getDeviceTypeName(type: string): string {
    switch (type.toLowerCase()) {
      case 'elevator':
        return 'Thang máy';
      case 'generator':
        return 'Máy phát điện';
      case 'pump':
        return 'Máy bơm';
      case 'security':
        return 'An ninh';
      case 'fire_safety':
        return 'Phòng cháy chữa cháy';
      case 'lighting':
        return 'Hệ thống chiếu sáng';
      case 'hvac':
        return 'Điều hòa không khí';
      default:
        return 'Thiết bị khác';
    }
  },
};
