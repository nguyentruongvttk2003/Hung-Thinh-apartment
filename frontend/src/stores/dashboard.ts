import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { DashboardStats, ChartData } from '@/types'
import api from '@/services/api'

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref<DashboardStats | null>(null)
  const recentActivities = ref<any[]>([])
  const loading = ref(false)

  const totalRevenue = computed(() => stats.value?.total_revenue || 0)
  const totalApartments = computed(() => stats.value?.total_apartments || 0)
  const totalResidents = computed(() => stats.value?.total_residents || 0)
  const pendingFeedbacks = computed(() => stats.value?.pending_feedbacks || 0)
  const upcomingMaintenance = computed(() => stats.value?.upcoming_maintenance || 0)
  const activeNotifications = computed(() => stats.value?.active_notifications || 0)

  async function fetchStats() {
    loading.value = true
    try {
      console.log('Fetching dashboard stats...')
      const response = await api.getDashboardStats()
      console.log('Dashboard stats response:', response)
      stats.value = response
    } catch (error) {
      console.error('Fetch stats error:', error)
    } finally {
      loading.value = false
    }
  }

  async function fetchRecentActivities() {
    try {
      console.log('Fetching recent activities...')
      const response = await api.getRecentActivities()
      console.log('Recent activities response:', response)
      recentActivities.value = response
    } catch (error) {
      console.error('Fetch recent activities error:', error)
    }
  }

  async function refreshDashboard() {
    await Promise.all([
      fetchStats(),
      fetchRecentActivities(),
    ])
  }

  return {
    stats,
    recentActivities,
    loading,
    totalRevenue,
    totalApartments,
    totalResidents,
    pendingFeedbacks,
    upcomingMaintenance,
    activeNotifications,
    fetchStats,
    fetchRecentActivities,
    refreshDashboard,
  }
}) 