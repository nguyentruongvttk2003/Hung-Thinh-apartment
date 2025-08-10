// API Configuration
export const API_CONFIG = {
  // Base URL for the Laravel backend
  // Use your computer's local IP that the phone can access
  BASE_URL: 'http://192.168.1.90:8000/api',
  
  // Alternative URLs for different environments
  LOCALHOST_URL: 'http://127.0.0.1:8000/api',
  TUNNEL_URL: 'http://localhost:8000/api', // For Expo tunnel mode
  
  // Timeout for requests (10 seconds)
  TIMEOUT: 10000,
  
  // Default headers
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  
  // Endpoints
  ENDPOINTS: {
    // Auth endpoints
    LOGIN: '/auth/login',
    REGISTER: '/auth/register',
    LOGOUT: '/auth/logout',
    REFRESH: '/auth/refresh',
    PROFILE: '/auth/profile',
    
    // Apartment endpoints
    APARTMENTS: '/apartments',
    APARTMENT_DETAILS: '/apartments',
    
    // Invoice endpoints
    INVOICES: '/invoices',
    INVOICE_PAY: '/invoices/pay',
    
    // Maintenance endpoints
    MAINTENANCE: '/maintenance',
    MAINTENANCE_CREATE: '/maintenance/create',
    
    // Notification endpoints
    NOTIFICATIONS: '/notifications',
    NOTIFICATIONS_READ: '/notifications/read',
    
    // Vote endpoints
    VOTES: '/votes',
    VOTE_SUBMIT: '/votes/submit',
    
    // Feedback endpoints
    FEEDBACKS: '/feedbacks',
    FEEDBACK_CREATE: '/feedbacks',
    FEEDBACK_RATE: '/feedbacks/rate',
    
    // Event endpoints
    EVENTS: '/events',
    EVENTS_UPCOMING: '/events/upcoming',
    
    // Dashboard endpoints
    DASHBOARD: '/dashboard',
    DASHBOARD_STATS: '/dashboard/stats',
    DASHBOARD_ACTIVITIES: '/dashboard/recent-activities',
  }
};

// Network status check
export const checkNetworkConnection = async (): Promise<boolean> => {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000);
    
    const response = await fetch(`${API_CONFIG.BASE_URL}/health`, {
      method: 'GET',
      signal: controller.signal,
    });
    
    clearTimeout(timeoutId);
    return response.ok;
  } catch (error) {
    console.log('Network check failed:', error);
    return false;
  }
};
