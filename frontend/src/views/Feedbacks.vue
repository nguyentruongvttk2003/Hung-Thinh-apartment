<template>
  <AppLayout>
    <div class="feedbacks-page">
    <div class="page-header">
      <h2>Quản lý phản ánh</h2>
    </div>

    <!-- Feedbacks Table -->
    <el-card>
        <el-table
          :data="feedbacks"
          v-loading="loading"
          style="width: 100%"
        >
          <el-table-column prop="title" label="Tiêu đề" />
          <el-table-column prop="type" label="Loại" width="120">
            <template #default="{ row }">
              <el-tag :type="getTypeTagType(row.type)">
                {{ getTypeLabel(row.type) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="status" label="Trạng thái" width="120">
            <template #default="{ row }">
              <el-tag :type="getStatusTagType(row.status)">
                {{ getStatusLabel(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="priority" label="Ưu tiên" width="100">
            <template #default="{ row }">
              <el-tag :type="getPriorityTagType(row.priority)">
                {{ getPriorityLabel(row.priority) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="Ngày tạo" width="150">
            <template #default="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </el-table-column>
          <el-table-column label="Thao tác" width="200" fixed="right">
            <template #default="{ row }">
              <el-button size="small" @click="viewFeedback(row)">Xem</el-button>
              <el-button size="small" type="primary" @click="assignFeedback(row)">Phân công</el-button>
            </template>
          </el-table-column>
        </el-table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
          <el-pagination
            v-model:current-page="currentPage"
            v-model:page-size="pageSize"
            :page-sizes="[10, 20, 50, 100]"
            :total="total"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </el-card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import AppLayout from '@/components/Layout/AppLayout.vue'
import api from '@/services/api'
import type { Feedback } from '@/types'

// Data
const feedbacks = ref<Feedback[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// Methods
async function loadFeedbacks() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getFeedbacks(params)
    feedbacks.value = response.data
    total.value = response.total
  } catch (error) {
    ElMessage.error('Không thể tải danh sách phản ánh')
  } finally {
    loading.value = false
  }
}

function getTypeLabel(type: string): string {
  const typeMap: Record<string, string> = {
    complaint: 'Khiếu nại',
    suggestion: 'Đề xuất',
    maintenance: 'Bảo trì'
  }
  return typeMap[type] || type
}

function getTypeTagType(type: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const typeTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    complaint: 'danger',
    suggestion: 'info',
    maintenance: 'warning'
  }
  return typeTagMap[type] || 'primary'
}

function getStatusLabel(status: string): string {
  const statusMap: Record<string, string> = {
    pending: 'Chờ xử lý',
    in_progress: 'Đang xử lý',
    resolved: 'Đã giải quyết',
    closed: 'Đã đóng'
  }
  return statusMap[status] || status
}

function getStatusTagType(status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const statusTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    pending: 'info',
    in_progress: 'warning',
    resolved: 'success',
    closed: 'danger'
  }
  return statusTagMap[status] || 'primary'
}

function getPriorityLabel(priority: string): string {
  const priorityMap: Record<string, string> = {
    low: 'Thấp',
    medium: 'Trung bình',
    high: 'Cao'
  }
  return priorityMap[priority] || priority
}

function getPriorityTagType(priority: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const priorityTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    low: 'info',
    medium: 'warning',
    high: 'danger'
  }
  return priorityTagMap[priority] || 'primary'
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('vi-VN')
}

function viewFeedback(feedback: Feedback) {
  ElMessage.info(`Xem phản ánh: ${feedback.title}`)
}

function assignFeedback(feedback: Feedback) {
  ElMessage.info(`Phân công phản ánh: ${feedback.title}`)
}

function handleSizeChange(size: number) {
  pageSize.value = size
  currentPage.value = 1
  loadFeedbacks()
}

function handleCurrentChange(page: number) {
  currentPage.value = page
  loadFeedbacks()
}

onMounted(() => {
  loadFeedbacks()
})
</script>

<style scoped>
.feedbacks-page {
  height: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #303133;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
</style> 