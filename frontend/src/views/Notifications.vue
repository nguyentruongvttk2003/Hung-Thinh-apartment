<template>
    <div class="notifications-page">
    <div class="page-header">
      <h2>Quản lý thông báo</h2>
      <el-button type="primary" @click="resetNotificationForm(); showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Tạo thông báo
      </el-button>
    </div>

      <!-- Notifications Table -->
      <el-card>
        <el-table
          :data="notifications"
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
              <el-button size="small" @click="viewNotification(row)">Xem</el-button>
              <el-button size="small" type="success" @click="sendNotification(row)">Gửi</el-button>
              <el-button size="small" type="danger" @click="deleteNotification(row)">Xóa</el-button>
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

      <!-- Create Dialog -->
      <el-dialog
        v-model="showCreateDialog"
        title="Tạo thông báo mới"
        width="600px"
      >
        <el-form
          ref="notificationFormRef"
          :model="notificationForm"
          :rules="notificationRules"
          label-width="100px"
        >
          <el-form-item label="Tiêu đề" prop="title">
            <el-input v-model="notificationForm.title" />
          </el-form-item>
          
          <el-form-item label="Nội dung" prop="content">
            <el-input
              v-model="notificationForm.content"
              type="textarea"
              :rows="4"
              placeholder="Nhập nội dung thông báo..."
            />
          </el-form-item>
          
          <el-form-item label="Loại" prop="type">
            <el-select v-model="notificationForm.type" placeholder="Chọn loại">
              <el-option label="Chung" value="general" />
              <el-option label="Bảo trì" value="maintenance" />
              <el-option label="Thanh toán" value="payment" />
              <el-option label="Sự kiện" value="event" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Ưu tiên" prop="priority">
            <el-select v-model="notificationForm.priority" placeholder="Chọn mức ưu tiên">
              <el-option label="Thấp" value="low" />
              <el-option label="Trung bình" value="medium" />
              <el-option label="Cao" value="high" />
            </el-select>
          </el-form-item>
        </el-form>
        
        <template #footer>
          <el-button @click="showCreateDialog = false">Hủy</el-button>
          <el-button type="primary" @click="saveNotification" :loading="saving">
            Tạo thông báo
          </el-button>
        </template>
      </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'

import api from '@/services/api'
import type { Notification } from '@/types'
import { Plus } from '@element-plus/icons-vue'

// Data
const notifications = ref<Notification[]>([])
const loading = ref(false)
const saving = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)
const notificationFormRef = ref<FormInstance>()

// Form interfaces
interface NotificationForm {
  title: string
  content: string
  type: 'general' | 'maintenance' | 'payment' | 'event'
  priority: 'low' | 'medium' | 'high'
}

// Form
const notificationForm = reactive<NotificationForm>({
  title: '',
  content: '',
  type: 'general',
  priority: 'medium'
})

const notificationRules: FormRules = {
  title: [
    { required: true, message: 'Vui lòng nhập tiêu đề', trigger: 'blur' }
  ],
  content: [
    { required: true, message: 'Vui lòng nhập nội dung', trigger: 'blur' }
  ],
  type: [
    { required: true, message: 'Vui lòng chọn loại', trigger: 'change' }
  ],
  priority: [
    { required: true, message: 'Vui lòng chọn mức ưu tiên', trigger: 'change' }
  ]
}

// Methods
async function loadNotifications() {
  loading.value = true
  try {
    console.log('Loading notifications with params:', {
      page: currentPage.value,
      per_page: pageSize.value
    })
    
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getNotifications(params)
    console.log('Notifications API response:', response)
    console.log('Response type:', typeof response)
    console.log('Response keys:', Object.keys(response))
    
    notifications.value = response.data || []
    total.value = response.total || 0
    console.log('Set notifications:', notifications.value.length, 'total:', total.value)
  } catch (error: any) {
    console.error('Notifications load error:', error)
    console.error('Error response:', error.response?.data)
    ElMessage.error('Không thể tải danh sách thông báo: ' + (error.message || 'Unknown error'))
  } finally {
    loading.value = false
  }
}

function getTypeLabel(type: string): string {
  const typeMap: Record<string, string> = {
    general: 'Chung',
    maintenance: 'Bảo trì',
    payment: 'Thanh toán',
    event: 'Sự kiện'
  }
  return typeMap[type] || type
}

function getTypeTagType(type: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const typeTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    general: 'info',
    maintenance: 'warning',
    payment: 'success',
    event: 'primary'
  }
  return typeTagMap[type] || 'primary'
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

function viewNotification(notification: Notification) {
  ElMessage.info(`Xem thông báo: ${notification.title}`)
}

async function sendNotification(notification: Notification) {
  try {
    await api.sendNotification(notification.id)
    ElMessage.success('Gửi thông báo thành công')
  } catch (error) {
    ElMessage.error('Không thể gửi thông báo')
  }
}

async function deleteNotification(notification: Notification) {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa thông báo "${notification.title}"?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    ElMessage.success('Xóa thông báo thành công')
    loadNotifications()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Không thể xóa thông báo')
    }
  }
}

async function saveNotification() {
  if (!notificationFormRef.value) return
  
  try {
    console.log('Validating notification form...')
    await notificationFormRef.value.validate()
    saving.value = true
    
    console.log('Notification form data:', notificationForm)
    
    const result = await api.createNotification(notificationForm)
    console.log('Create notification result:', result)
    ElMessage.success('Tạo thông báo thành công')
    
    showCreateDialog.value = false
    resetNotificationForm()
    loadNotifications()
  } catch (error: any) {
    console.error('Save notification error:', error)
    console.error('Error response:', error.response?.data)
    
    let errorMessage = 'Có lỗi xảy ra'
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
    saving.value = false
  }
}

function handleSizeChange(size: number) {
  pageSize.value = size
  currentPage.value = 1
  loadNotifications()
}

function handleCurrentChange(page: number) {
  currentPage.value = page
  loadNotifications()
}

function resetNotificationForm() {
  Object.assign(notificationForm, {
    title: '',
    content: '',
    type: 'general',
    priority: 'medium'
  })
  notificationFormRef.value?.resetFields()
}

onMounted(() => {
  loadNotifications()
})
</script>

<style scoped>
.notifications-page {
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