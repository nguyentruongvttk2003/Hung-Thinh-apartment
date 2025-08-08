import axios from 'axios'
import type { AxiosInstance, AxiosResponse } from 'axios'
import type { 
  User, 
  Apartment, 
  Notification, 
  Feedback, 
  Invoice, 
  Payment, 
  Device, 
  Maintenance, 
  Event, 
  Vote,
  ApiResponse,
  PaginatedResponse,
  DashboardStats
} from '@/types'

class ApiService {
  private api: AxiosInstance

  constructor() {
    this.api = axios.create({
      baseURL: 'http://localhost:8000/api',
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    })

    // Request interceptor to add auth token
    this.api.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('auth_token')
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
        return config
      },
      (error) => {
        return Promise.reject(error)
      }
    )

    // Response interceptor to handle errors
    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          localStorage.removeItem('auth_token')
          window.location.href = '/login'
        }
        return Promise.reject(error)
      }
    )
  }

  // Auth endpoints
  async login(email: string, password: string): Promise<{ access_token: string; token_type: string; expires_in: number; user: User }> {
    console.log('API login called with:', { email })
    const response = await this.api.post('/auth/login', { email, password })
    console.log('API login response:', response)
    console.log('API response.data:', response.data)
    return response.data
  }

  async register(userData: Partial<User> & { password: string }): Promise<ApiResponse<{ access_token: string; token_type: string; expires_in: number; user: User }>> {
    const response = await this.api.post('/auth/register', userData)
    return response.data
  }

  async logout(): Promise<ApiResponse<null>> {
    const response = await this.api.post('/auth/logout')
    return response.data
  }

  async getProfile(): Promise<ApiResponse<User>> {
    const response = await this.api.get('/auth/profile')
    return response.data
  }

  async updateProfile(userData: Partial<User>): Promise<ApiResponse<User>> {
    const response = await this.api.put('/auth/profile', userData)
    return response.data
  }

  // Dashboard endpoints
  async getDashboardStats(): Promise<DashboardStats> {
    const response = await this.api.get('/dashboard/stats')
    return response.data
  }

  async getRecentActivities(): Promise<any[]> {
    const response = await this.api.get('/dashboard/recent-activities')
    return response.data
  }

  // Apartment endpoints
  async getApartments(params?: any): Promise<PaginatedResponse<Apartment>> {
    const response = await this.api.get('/apartments', { params })
    return response.data
  }

  async getApartment(id: number): Promise<ApiResponse<Apartment>> {
    const response = await this.api.get(`/apartments/${id}`)
    return response.data
  }

  async createApartment(apartmentData: Partial<Apartment>): Promise<ApiResponse<Apartment>> {
    const response = await this.api.post('/apartments', apartmentData)
    return response.data
  }

  async updateApartment(id: number, apartmentData: Partial<Apartment>): Promise<ApiResponse<Apartment>> {
    const response = await this.api.put(`/apartments/${id}`, apartmentData)
    return response.data
  }

  async deleteApartment(id: number): Promise<ApiResponse<null>> {
    const response = await this.api.delete(`/apartments/${id}`)
    return response.data
  }

  // User endpoints
  async getUsers(params?: any): Promise<PaginatedResponse<User>> {
    const response = await this.api.get('/users', { params })
    return response.data
  }

  async getUser(id: number): Promise<ApiResponse<User>> {
    const response = await this.api.get(`/users/${id}`)
    return response.data
  }

  async createUser(userData: Partial<User> & { password: string }): Promise<ApiResponse<User>> {
    const response = await this.api.post('/users', userData)
    return response.data
  }

  async updateUser(id: number, userData: Partial<User>): Promise<ApiResponse<User>> {
    const response = await this.api.put(`/users/${id}`, userData)
    return response.data
  }

  async deleteUser(id: number): Promise<ApiResponse<null>> {
    const response = await this.api.delete(`/users/${id}`)
    return response.data
  }

  // Notification endpoints
  async getNotifications(params?: any): Promise<PaginatedResponse<Notification>> {
    const response = await this.api.get('/notifications', { params })
    return response.data
  }

  async createNotification(notificationData: Partial<Notification>): Promise<ApiResponse<Notification>> {
    const response = await this.api.post('/notifications', notificationData)
    return response.data
  }

  async sendNotification(id: number): Promise<ApiResponse<Notification>> {
    const response = await this.api.post(`/notifications/${id}/send`)
    return response.data
  }

  // Feedback endpoints
  async getFeedbacks(params?: any): Promise<PaginatedResponse<Feedback>> {
    const response = await this.api.get('/feedbacks', { params })
    return response.data
  }

  async createFeedback(feedbackData: Partial<Feedback>): Promise<ApiResponse<Feedback>> {
    const response = await this.api.post('/feedbacks', feedbackData)
    return response.data
  }

  async assignFeedback(id: number, assignedTo: number): Promise<ApiResponse<Feedback>> {
    const response = await this.api.post(`/feedbacks/${id}/assign`, { assigned_to: assignedTo })
    return response.data
  }

  // Invoice endpoints
  async getInvoices(params?: any): Promise<PaginatedResponse<Invoice>> {
    const response = await this.api.get('/invoices', { params })
    return response.data
  }

  async createInvoice(invoiceData: Partial<Invoice>): Promise<ApiResponse<Invoice>> {
    const response = await this.api.post('/invoices', invoiceData)
    return response.data
  }

  async bulkCreateInvoices(invoicesData: Partial<Invoice>[]): Promise<ApiResponse<Invoice[]>> {
    const response = await this.api.post('/invoices/bulk-create', { invoices: invoicesData })
    return response.data
  }

  // Payment endpoints
  async getPayments(params?: any): Promise<PaginatedResponse<Payment>> {
    const response = await this.api.get('/payments', { params })
    return response.data
  }

  async createPayment(paymentData: Partial<Payment>): Promise<ApiResponse<Payment>> {
    const response = await this.api.post('/payments', paymentData)
    return response.data
  }

  // Device endpoints
  async getDevices(params?: any): Promise<PaginatedResponse<Device>> {
    const response = await this.api.get('/devices', { params })
    return response.data
  }

  async createDevice(deviceData: Partial<Device>): Promise<ApiResponse<Device>> {
    const response = await this.api.post('/devices', deviceData)
    return response.data
  }

  // Maintenance endpoints
  async getMaintenances(params?: any): Promise<PaginatedResponse<Maintenance>> {
    const response = await this.api.get('/maintenances', { params })
    return response.data
  }

  async createMaintenance(maintenanceData: Partial<Maintenance>): Promise<ApiResponse<Maintenance>> {
    const response = await this.api.post('/maintenances', maintenanceData)
    return response.data
  }

  // Event endpoints
  async getEvents(params?: any): Promise<PaginatedResponse<Event>> {
    const response = await this.api.get('/events', { params })
    return response.data
  }

  async createEvent(eventData: Partial<Event>): Promise<ApiResponse<Event>> {
    const response = await this.api.post('/events', eventData)
    return response.data
  }

  // Vote endpoints
  async getVotes(params?: any): Promise<PaginatedResponse<Vote>> {
    const response = await this.api.get('/votes', { params })
    return response.data
  }

  async createVote(voteData: Partial<Vote>): Promise<ApiResponse<Vote>> {
    const response = await this.api.post('/votes', voteData)
    return response.data
  }

  async submitVote(voteId: number, optionId: number): Promise<ApiResponse<any>> {
    const response = await this.api.post(`/votes/${voteId}/vote`, { option_id: optionId })
    return response.data
  }
}

export default new ApiService() 