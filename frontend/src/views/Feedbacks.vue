<template>
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
          <el-table-column label="Người phụ trách" width="180">
            <template #default="{ row }">
              <div v-if="row.assigned_technician">
                <el-tag type="success" size="small">
                  {{ row.assigned_technician.name }}
                </el-tag>
                <div class="text-xs text-gray-500 mt-1">
                  {{ formatDate(row.assigned_at) }}
                </div>
              </div>
              <el-tag v-else type="info" size="small">Chưa phân công</el-tag>
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

      <!-- Assignment Dialog -->
      <el-dialog
        v-model="showAssignDialog"
        title="Phân công phản ánh"
        width="500px"
      >
        <el-form
          ref="assignFormRef"
          :model="assignForm"
          :rules="assignRules"
          label-width="120px"
        >
          <el-form-item label="Phản ánh" prop="title">
            <el-input :value="selectedFeedback?.title" readonly />
          </el-form-item>
          
          <el-form-item label="Kỹ thuật viên" prop="assigned_to">
            <el-select 
              v-model="assignForm.assigned_to" 
              placeholder="Chọn kỹ thuật viên"
              style="width: 100%"
              :loading="loadingTechnicians"
            >
              <el-option
                v-for="tech in technicians"
                :key="tech.id"
                :label="`${tech.name} (${tech.email})`"
                :value="tech.id"
              />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Ghi chú" prop="notes">
            <el-input
              v-model="assignForm.notes"
              type="textarea"
              :rows="3"
              placeholder="Ghi chú cho kỹ thuật viên..."
            />
          </el-form-item>
        </el-form>
        
        <template #footer>
          <el-button @click="showAssignDialog = false">Hủy</el-button>
          <el-button type="primary" @click="saveAssignment" :loading="assigning">
            Phân công
          </el-button>
        </template>
      </el-dialog>

      <!-- View Feedback Dialog -->
      <el-dialog
        v-model="showViewDialog"
        title="Chi tiết phản ánh"
        width="700px"
      >
        <div v-if="viewingFeedback" class="feedback-detail">
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Tiêu đề:</label>
                <p>{{ viewingFeedback.title }}</p>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Loại:</label>
                <el-tag :type="getTypeTagType(viewingFeedback.type)">
                  {{ getTypeLabel(viewingFeedback.type) }}
                </el-tag>
              </div>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Trạng thái:</label>
                <el-tag :type="getStatusTagType(viewingFeedback.status)">
                  {{ getStatusLabel(viewingFeedback.status) }}
                </el-tag>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Ưu tiên:</label>
                <el-tag :type="getPriorityTagType(viewingFeedback.priority)">
                  {{ getPriorityLabel(viewingFeedback.priority) }}
                </el-tag>
              </div>
            </el-col>
          </el-row>

          <div class="detail-item">
            <label>Mô tả:</label>
            <p class="description">{{ viewingFeedback.description || 'Không có mô tả' }}</p>
          </div>

          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Ngày tạo:</label>
                <p>{{ formatDate(viewingFeedback.created_at) }}</p>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Người tạo:</label>
                <p>{{ viewingFeedback.created_by || 'N/A' }}</p>
              </div>
            </el-col>
          </el-row>

          <div v-if="viewingFeedback.assigned_technician" class="detail-item">
            <label>Người phụ trách:</label>
            <div class="assigned-info">
              <el-tag type="success">{{ viewingFeedback.assigned_technician.name }}</el-tag>
              <p class="assigned-date">Phân công lúc: {{ viewingFeedback.assigned_at ? formatDate(viewingFeedback.assigned_at) : 'N/A' }}</p>
            </div>
          </div>
        </div>

        <template #footer>
          <el-button @click="showViewDialog = false">Đóng</el-button>
          <el-button v-if="!viewingFeedback?.assigned_technician" type="primary" @click="assignFeedbackFromView">
            Phân công
          </el-button>
        </template>
      </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'

import api from '@/services/api'
import type { Feedback, User } from '@/types'

// Data
const feedbacks = ref<Feedback[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// Assignment data
const showAssignDialog = ref(false)
const loadingTechnicians = ref(false)
const assigning = ref(false)
const technicians = ref<User[]>([])
const selectedFeedback = ref<Feedback | null>(null)
const assignFormRef = ref<FormInstance>()

// View data
const showViewDialog = ref(false)
const viewingFeedback = ref<Feedback | null>(null)

// Assignment form
const assignForm = reactive({
  assigned_to: '',
  notes: ''
})

const assignRules: FormRules = {
  assigned_to: [
    { required: true, message: 'Vui lòng chọn kỹ thuật viên', trigger: 'change' }
  ]
}

// Methods
async function loadFeedbacks() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getFeedbacks(params)
    console.log('Feedbacks API response:', response)
    console.log('Response data type:', typeof response.data)
    console.log('Response data length:', Array.isArray(response.data) ? response.data.length : 'not array')
    
    // Handle PaginatedResponse structure
    if (response.data && Array.isArray(response.data)) {
      feedbacks.value = response.data
      total.value = response.total || 0
    } else {
      console.warn('Unexpected response structure:', response)
      feedbacks.value = []
      total.value = 0
    }
    
    console.log('Final feedbacks count:', feedbacks.value.length)
  } catch (error: any) {
    console.error('Load feedbacks error:', error)
    console.error('Error response:', error.response?.data)
    ElMessage.error('Không thể tải danh sách phản ánh: ' + (error.message || 'Unknown error'))
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
  console.log('Viewing feedback:', feedback)
  viewingFeedback.value = feedback
  showViewDialog.value = true
}

async function assignFeedback(feedback: Feedback) {
  try {
    console.log('Opening assign dialog for feedback:', feedback)
    selectedFeedback.value = feedback
    resetAssignForm()
    
    // Load technicians if not loaded yet
    if (technicians.value.length === 0) {
      await loadTechnicians()
    }
    
    showAssignDialog.value = true
  } catch (error: any) {
    console.error('Error opening assign dialog:', error)
    ElMessage.error('Không thể mở dialog phân công')
  }
}

async function loadTechnicians() {
  loadingTechnicians.value = true
  try {
    console.log('Loading technicians...')
    const response = await api.getTechnicians()
    technicians.value = response.data || []
    console.log('Loaded technicians:', technicians.value.length)
  } catch (error: any) {
    console.error('Error loading technicians:', error)
    ElMessage.error('Không thể tải danh sách kỹ thuật viên')
  } finally {
    loadingTechnicians.value = false
  }
}

async function saveAssignment() {
  if (!assignFormRef.value || !selectedFeedback.value) return
  
  try {
    console.log('Validating assignment form...')
    await assignFormRef.value.validate()
    assigning.value = true
    
    console.log('Assignment form data:', assignForm)
    console.log('Selected feedback:', selectedFeedback.value.id)
    
    const result = await api.assignFeedback(selectedFeedback.value.id, {
      assigned_to: Number(assignForm.assigned_to),
      notes: assignForm.notes
    })
    
    console.log('Assignment result:', result)
    ElMessage.success('Phân công phản ánh thành công')
    
    showAssignDialog.value = false
    loadFeedbacks() // Reload to show updated assignment
  } catch (error: any) {
    console.error('Assignment error:', error)
    console.error('Error response:', error.response?.data)
    
    let errorMessage = 'Có lỗi xảy ra khi phân công'
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0]
      errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
    } else if (error.message) {
      errorMessage = error.message
    }
    
    ElMessage.error(errorMessage)
  } finally {
    assigning.value = false
  }
}

function assignFeedbackFromView() {
  if (viewingFeedback.value) {
    showViewDialog.value = false
    assignFeedback(viewingFeedback.value)
  }
}

function resetAssignForm() {
  Object.assign(assignForm, {
    assigned_to: '',
    notes: ''
  })
  assignFormRef.value?.resetFields()
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

.feedback-detail .detail-item {
  margin-bottom: 16px;
}

.feedback-detail .detail-item label {
  font-weight: 600;
  color: #606266;
  display: block;
  margin-bottom: 4px;
}

.feedback-detail .detail-item p {
  margin: 0;
  color: #303133;
}

.feedback-detail .description {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 4px;
  border: 1px solid #e0e6ed;
  white-space: pre-wrap;
}

.feedback-detail .assigned-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.feedback-detail .assigned-date {
  font-size: 12px;
  color: #909399;
  margin: 0;
}
</style> 