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
        console.log('Request interceptor - token:', token ? 'exists' : 'missing')
        console.log('Request URL:', config.url)
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
        return config
      },
      (error) => {
        console.error('Request interceptor error:', error)
        return Promise.reject(error)
      }
    )

    // Response interceptor to handle errors
    this.api.interceptors.response.use(
      (response) => {
        console.log('Response interceptor - success:', response.config.url, response.status)
        return response
      },
      (error) => {
        console.error('Response interceptor - error:', error.config?.url, error.response?.status)
        console.error('Error data:', error.response?.data)
        
        if (error.response?.status === 401) {
          console.log('Unauthorized - removing token and redirecting')
          localStorage.removeItem('auth_token')
          window.location.href = '/login'
        }
        return Promise.reject(error)
      }
    )
  }

  // Auth endpoints
  async login(email: string, password: string): Promise<{ success: boolean; data: { token: string; token_type: string; expires_in: number; user: User } }> {
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
    console.log('API getApartments called with params:', params)
    console.log('API baseURL:', this.api.defaults.baseURL)
    console.log('API headers:', this.api.defaults.headers)
    
    try {
      const response = await this.api.get('/apartments', { params })
      console.log('Raw axios response:', response)
      console.log('Response status:', response.status)
      console.log('Response data:', response.data)
      return response.data
    } catch (error: any) {
      console.error('API getApartments error:', error)
      console.error('Error status:', error.response?.status)
      console.error('Error data:', error.response?.data)
      throw error
    }
  }

  async getApartment(id: number): Promise<ApiResponse<Apartment>> {
    const response = await this.api.get(`/apartments/${id}`)
    return response.data
  }

  async createApartment(apartmentData: Partial<Apartment>): Promise<ApiResponse<Apartment>> {
    console.log('API createApartment called with data:', apartmentData)
    
    try {
      const response = await this.api.post('/apartments', apartmentData)
      console.log('Create apartment response:', response)
      return response.data
    } catch (error: any) {
      console.error('API createApartment error:', error)
      console.error('Create apartment error response:', error.response?.data)
      throw error
    }
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
    console.log('API createUser called with data:', userData)
    
    try {
      const response = await this.api.post('/users', userData)
      console.log('Create user response:', response)
      return response.data
    } catch (error: any) {
      console.error('API createUser error:', error)
      console.error('Create user error response:', error.response?.data)
      console.error('Error status:', error.response?.status)
      console.error('Error headers:', error.response?.headers)
      if (error.response?.data?.errors) {
        console.error('Validation errors detail:', error.response.data.errors)
      }
      throw error
    }
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
    console.log('API getNotifications called with params:', params)
    console.log('API baseURL:', this.api.defaults.baseURL)
    console.log('API headers:', this.api.defaults.headers)
    
    try {
      const response = await this.api.get('/notifications', { params })
      console.log('Raw notifications axios response:', response)
      console.log('Notifications response status:', response.status)
      console.log('Notifications response data:', response.data)
      return response.data
    } catch (error: any) {
      console.error('API getNotifications error:', error)
      console.error('Notifications error status:', error.response?.status)
      console.error('Notifications error data:', error.response?.data)
      throw error
    }
  }

  async createNotification(notificationData: Partial<Notification>): Promise<ApiResponse<Notification>> {
    console.log('API createNotification called with data:', notificationData)
    
    try {
      const response = await this.api.post('/notifications', notificationData)
      console.log('Create notification response:', response)
      return response.data
    } catch (error: any) {
      console.error('API createNotification error:', error)
      console.error('Create notification error response:', error.response?.data)
      console.error('Error status:', error.response?.status)
      if (error.response?.data?.errors) {
        console.error('Validation errors detail:', error.response.data.errors)
      }
      throw error
    }
  }

  async sendNotification(id: number): Promise<ApiResponse<Notification>> {
    const response = await this.api.post(`/notifications/${id}/send`)
    return response.data
  }

  async deleteNotification(id: number): Promise<ApiResponse<null>> {
    console.log('API deleteNotification called for ID:', id)
    
    try {
      const response = await this.api.delete(`/notifications/${id}`)
      console.log('Delete notification response:', response)
      return response.data
    } catch (error: any) {
      console.error('API deleteNotification error:', error)
      throw error
    }
  }

  // Feedback endpoints
  async getFeedbacks(params?: any): Promise<PaginatedResponse<Feedback>> {
    console.log('API getFeedbacks called with params:', params)
    
    try {
      const response = await this.api.get('/feedbacks', { params })
      console.log('getFeedbacks raw response:', response)
      console.log('getFeedbacks response data:', response.data)
      return response.data
    } catch (error: any) {
      console.error('API getFeedbacks error:', error)
      console.error('Error status:', error.response?.status)
      console.error('Error data:', error.response?.data)
      throw error
    }
  }

  async createFeedback(feedbackData: Partial<Feedback>): Promise<ApiResponse<Feedback>> {
    const response = await this.api.post('/feedbacks', feedbackData)
    return response.data
  }

  async assignFeedback(id: number, assignData: { assigned_to: number; notes?: string }): Promise<ApiResponse<Feedback>> {
    console.log('API assignFeedback called', { id, assignData })
    
    try {
      const response = await this.api.post(`/feedbacks/${id}/assign`, assignData)
      console.log('Assign feedback response:', response)
      return response.data
    } catch (error: any) {
      console.error('API assignFeedback error:', error)
      console.error('Assign feedback error response:', error.response?.data)
      throw error
    }
  }

  async getTechnicians(): Promise<ApiResponse<User[]>> {
    console.log('API getTechnicians called')
    
    try {
      const response = await this.api.get('/users/technicians')
      console.log('Get technicians response:', response)
      return response.data
    } catch (error: any) {
      console.error('API getTechnicians error:', error)
      throw error
    }
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

  async updateInvoice(id: number, invoiceData: Partial<Invoice>): Promise<ApiResponse<Invoice>> {
    console.log('API updateInvoice called', { id, invoiceData })
    
    try {
      const response = await this.api.put(`/invoices/${id}`, invoiceData)
      console.log('Update invoice response:', response)
      return response.data
    } catch (error: any) {
      console.error('API updateInvoice error:', error)
      throw error
    }
  }

  async deleteInvoice(id: number): Promise<ApiResponse<null>> {
    console.log('API deleteInvoice called for ID:', id)
    
    try {
      const response = await this.api.delete(`/invoices/${id}`)
      console.log('Delete invoice response:', response)
      return response.data
    } catch (error: any) {
      console.error('API deleteInvoice error:', error)
      throw error
    }
  }

  async bulkCreateInvoices(invoicesData: Partial<Invoice>[]): Promise<ApiResponse<Invoice[]>> {
    const response = await this.api.post('/invoices/bulk-create', { invoices: invoicesData })
    return response.data
  }

  // Payment endpoints
  async getPayments(params?: any): Promise<PaginatedResponse<Payment>> {
    console.log('API getPayments called with params:', params)
    console.log('API baseURL:', this.api.defaults.baseURL)
    console.log('API headers:', this.api.defaults.headers)
    
    try {
      const response = await this.api.get('/payments', { params })
      console.log('Raw payments axios response:', response)
      console.log('Payments response status:', response.status)
      console.log('Payments response data:', response.data)
      return response.data
    } catch (error: any) {
      console.error('API getPayments error:', error)
      console.error('Payments error status:', error.response?.status)
      console.error('Payments error data:', error.response?.data)
      throw error
    }
  }

  async getPayment(id: number): Promise<ApiResponse<Payment>> {
    const response = await this.api.get(`/payments/${id}`)
    return response.data
  }

  async createPayment(paymentData: Partial<Payment>): Promise<ApiResponse<Payment>> {
    const response = await this.api.post('/payments', paymentData)
    return response.data
  }

  async updatePayment(id: number, paymentData: Partial<Payment>): Promise<ApiResponse<Payment>> {
    const response = await this.api.put(`/payments/${id}`, paymentData)
    return response.data
  }

  async deletePayment(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/payments/${id}`)
    return response.data
  }

  async processPayment(id: number): Promise<ApiResponse<Payment>> {
    const response = await this.api.post(`/payments/${id}/process`)
    return response.data
  }

  async getPaymentsByInvoice(invoiceId: number): Promise<ApiResponse<Payment[]>> {
    const response = await this.api.get(`/payments/invoice/${invoiceId}`)
    return response.data
  }

  async getMyPayments(): Promise<ApiResponse<Payment[]>> {
    const response = await this.api.get('/payments/my-payments')
    return response.data
  }

  async getPaymentStats(params?: any): Promise<ApiResponse<any>> {
    const response = await this.api.get('/payments/stats', { params })
    return response.data
  }

  // Device endpoints
  async getDevices(params?: any): Promise<PaginatedResponse<Device>> {
    const response = await this.api.get('/devices', { params })
    return response.data
  }

  async getDevice(id: number): Promise<ApiResponse<Device>> {
    const response = await this.api.get(`/devices/${id}`)
    return response.data
  }

  async createDevice(deviceData: Partial<Device>): Promise<ApiResponse<Device>> {
    const response = await this.api.post('/devices', deviceData)
    return response.data
  }

  async updateDevice(id: number, deviceData: Partial<Device>): Promise<ApiResponse<Device>> {
    const response = await this.api.put(`/devices/${id}`, deviceData)
    return response.data
  }

  async deleteDevice(id: number): Promise<ApiResponse<null>> {
    const response = await this.api.delete(`/devices/${id}`)
    return response.data
  }

  // Maintenance endpoints
  async getMaintenances(params?: any): Promise<PaginatedResponse<Maintenance>> {
    const response = await this.api.get('/maintenances', { params })
    return response.data
  }

  async getMaintenance(id: number): Promise<ApiResponse<Maintenance>> {
    const response = await this.api.get(`/maintenances/${id}`)
    console.log('getMaintenance raw response:', response.data)
    return response.data
  }

  async createMaintenance(maintenanceData: Partial<Maintenance>): Promise<ApiResponse<Maintenance>> {
    const response = await this.api.post('/maintenances', maintenanceData)
    console.log('createMaintenance raw response:', response.data)
    return response.data
  }

  async updateMaintenance(id: number, maintenanceData: Partial<Maintenance>): Promise<ApiResponse<Maintenance>> {
    const response = await this.api.put(`/maintenances/${id}`, maintenanceData)
    console.log('updateMaintenance raw response:', response.data)
    return response.data
  }

  async deleteMaintenance(id: number): Promise<ApiResponse<any>> {
    const response = await this.api.delete(`/maintenances/${id}`)
    console.log('deleteMaintenance raw response:', response.data)
    return response.data
  }

  // Event endpoints
  async getEvents(params?: any): Promise<PaginatedResponse<Event>> {
    const response = await this.api.get('/events', { params })
    console.log('getEvents response:', response.data)
    return response.data
  }

  async getEvent(id: number): Promise<ApiResponse<Event>> {
    const response = await this.api.get(`/events/${id}`)
    return response.data
  }

  async createEvent(eventData: Partial<Event>): Promise<ApiResponse<Event>> {
    const response = await this.api.post('/events', eventData)
    return response.data
  }

  async updateEvent(id: number, eventData: Partial<Event>): Promise<ApiResponse<Event>> {
    const response = await this.api.put(`/events/${id}`, eventData)
    return response.data
  }

  async deleteEvent(id: number): Promise<ApiResponse<any>> {
    const response = await this.api.delete(`/events/${id}`)
    return response.data
  }

  // Vote endpoints
  async getVotes(params?: any): Promise<PaginatedResponse<Vote>> {
    const response = await this.api.get('/votes', { params })
    return response.data
  }

  async getVote(id: number): Promise<ApiResponse<Vote>> {
    const response = await this.api.get(`/votes/${id}`)
    return response.data
  }

  async createVote(voteData: Partial<Vote>): Promise<ApiResponse<Vote>> {
    const response = await this.api.post('/votes', voteData)
    return response.data
  }

  async updateVote(id: number, voteData: Partial<Vote>): Promise<ApiResponse<Vote>> {
    const response = await this.api.put(`/votes/${id}`, voteData)
    return response.data
  }

  async deleteVote(id: number): Promise<ApiResponse<any>> {
    const response = await this.api.delete(`/votes/${id}`)
    return response.data
  }

  async submitVote(voteId: number, optionId: number): Promise<ApiResponse<any>> {
    const response = await this.api.post(`/votes/${voteId}/vote`, { option_id: optionId })
    return response.data
  }
}

export default new ApiService() 