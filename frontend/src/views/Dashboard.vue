<template>
  <AppLayout>
    <div class="dashboard">
    <!-- Stats Cards -->
    <div class="stats-grid">
      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon apartments">
            <el-icon><House /></el-icon>
          </div>
          <div class="stat-info">
            <h3>{{ dashboardStore.totalApartments }}</h3>
            <p>Tổng căn hộ</p>
          </div>
        </div>
      </el-card>

      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon residents">
            <el-icon><User /></el-icon>
          </div>
          <div class="stat-info">
            <h3>{{ dashboardStore.totalResidents }}</h3>
            <p>Tổng cư dân</p>
          </div>
        </div>
      </el-card>

      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon revenue">
            <el-icon><Money /></el-icon>
          </div>
          <div class="stat-info">
            <h3>{{ formatCurrency(dashboardStore.totalRevenue) }}</h3>
            <p>Doanh thu</p>
          </div>
        </div>
      </el-card>

      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon feedbacks">
            <el-icon><ChatDotRound /></el-icon>
          </div>
          <div class="stat-info">
            <h3>{{ dashboardStore.pendingFeedbacks }}</h3>
            <p>Phản ánh chờ xử lý</p>
          </div>
            </div>
          </el-card>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <span>Thống kê hóa đơn</span>
              </div>
            </template>
            <div class="chart-container">
              <canvas ref="invoiceChartRef"></canvas>
            </div>
          </el-card>

          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <span>Trạng thái căn hộ</span>
              </div>
            </template>
            <div class="chart-container">
              <canvas ref="apartmentChartRef"></canvas>
            </div>
          </el-card>
        </div>

        <!-- Recent Activities -->
        <el-card class="activities-card">
          <template #header>
            <div class="card-header">
              <span>Hoạt động gần đây</span>
              <el-button text @click="refreshDashboard">
                <el-icon><Refresh /></el-icon>
                Làm mới
              </el-button>
            </div>
          </template>
          
          <div class="activities-list">
            <div
              v-for="activity in dashboardStore.recentActivities"
              :key="activity.id"
              class="activity-item"
            >
              <div class="activity-icon">
                <el-icon><Clock /></el-icon>
              </div>
              <div class="activity-content">
                <p class="activity-text">{{ activity.description }}</p>
                <span class="activity-time">{{ formatTime(activity.created_at) }}</span>
              </div>
            </div>
            
            <div v-if="dashboardStore.recentActivities.length === 0" class="no-activities">
              <el-empty description="Không có hoạt động nào" />
            </div>
          </div>
        </el-card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import AppLayout from '@/components/Layout/AppLayout.vue'
import { useDashboardStore } from '@/stores/dashboard'
import { House, User, Money, ChatDotRound, Refresh, Clock } from '@element-plus/icons-vue'

Chart.register(...registerables)

const dashboardStore = useDashboardStore()
const invoiceChartRef = ref<HTMLCanvasElement>()
const apartmentChartRef = ref<HTMLCanvasElement>()

let invoiceChart: Chart | null = null
let apartmentChart: Chart | null = null

onMounted(async () => {
  console.log('Dashboard mounted, loading data...')
  try {
    await dashboardStore.refreshDashboard()
    console.log('Dashboard data loaded:', {
      stats: dashboardStore.stats,
      totalApartments: dashboardStore.totalApartments,
      totalResidents: dashboardStore.totalResidents,
      loading: dashboardStore.loading
    })
    await nextTick()
    initCharts()
  } catch (error) {
    console.error('Error loading dashboard:', error)
  }
})

function initCharts() {
  // Invoice Chart
  if (invoiceChartRef.value) {
    const ctx = invoiceChartRef.value.getContext('2d')
    if (ctx) {
      invoiceChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
          datasets: [{
            label: 'Doanh thu (triệu VNĐ)',
            data: [12, 19, 3, 5, 2, 3, 15, 18, 22, 25, 28, 30],
            borderColor: '#409EFF',
            backgroundColor: 'rgba(64, 158, 255, 0.1)',
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      })
    }
  }

  // Apartment Status Chart
  if (apartmentChartRef.value) {
    const ctx = apartmentChartRef.value.getContext('2d')
    if (ctx) {
      apartmentChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Đã thuê', 'Trống', 'Bảo trì'],
          datasets: [{
            data: [280, 15, 5],
            backgroundColor: ['#67C23A', '#E6A23C', '#F56C6C'],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      })
    }
  }
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount)
}

function formatTime(timeString: string): string {
  const date = new Date(timeString)
  return date.toLocaleString('vi-VN')
}

async function refreshDashboard() {
  await dashboardStore.refreshDashboard()
}
</script>

<style scoped>
.dashboard {
  height: 100%;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.stat-card {
  border: none;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.stat-content {
  display: flex;
  align-items: center;
  padding: 10px 0;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  font-size: 24px;
  color: white;
}

.stat-icon.apartments {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.residents {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.revenue {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-icon.feedbacks {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-info h3 {
  margin: 0 0 4px 0;
  font-size: 24px;
  font-weight: 600;
  color: #303133;
}

.stat-info p {
  margin: 0;
  color: #909399;
  font-size: 14px;
}

.charts-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.chart-card {
  border: none;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.chart-container {
  height: 300px;
  position: relative;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.activities-card {
  border: none;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.activities-list {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: #f0f9ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: #409EFF;
}

.activity-content {
  flex: 1;
}

.activity-text {
  margin: 0 0 4px 0;
  color: #303133;
  font-size: 14px;
}

.activity-time {
  color: #909399;
  font-size: 12px;
}

.no-activities {
  padding: 40px 0;
}

@media (max-width: 768px) {
  .charts-row {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}
</style> 