import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isResident = computed(() => user.value?.role === 'resident')
  const isTechnician = computed(() => user.value?.role === 'technician')
  const isAccountant = computed(() => user.value?.role === 'accountant')

  async function login(email: string, password: string) {
    loading.value = true
    try {
      console.log('Sending login request with:', { email })
      const response = await api.login(email, password)
      console.log('Login response:', response)
      
      // Backend returns wrapped in ApiResponse with 'data' field
      if (response.success && response.data && response.data.user && response.data.token) {
        user.value = response.data.user
        token.value = response.data.token
        localStorage.setItem('auth_token', response.data.token)
        console.log('Auth state updated:', {
          user: user.value,
          token: !!token.value,
          isAuthenticated: isAuthenticated.value
        })
      } else {
        throw new Error('Invalid response structure')
      }
      return response
    } catch (error: any) {
      console.error('Login error:', error)
      console.error('Error response:', error.response?.data)
      // Clear any partial state
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      throw error
    } finally {
      loading.value = false
    }
  }

  async function register(userData: Partial<User> & { password: string }) {
    loading.value = true
    try {
      const response = await api.register(userData)
      user.value = response.data.user
      token.value = response.data.access_token
      localStorage.setItem('auth_token', response.data.access_token)
      return response
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.logout()
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
    }
  }

  async function fetchProfile() {
    if (!token.value) return
    
    try {
      const response = await api.getProfile()
      user.value = response.data
    } catch (error) {
      console.error('Fetch profile error:', error)
      logout()
    }
  }

  function initializeAuth() {
    if (token.value) {
      fetchProfile()
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isAdmin,
    isResident,
    isTechnician,
    isAccountant,
    login,
    register,
    logout,
    fetchProfile,
    initializeAuth,
  }
}) 