<template>
  <div class="events-page">
    <div class="page-header">
      <h1>Quản lý Sự kiện</h1>
      </div>

      <DataTable
        title="Danh sách sự kiện"
        :data="events"
        :columns="columns"
        :loading="loading"
        :total="total"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :filters="filters"
        :actions="actions"
        :search-keys="['title', 'location', 'description']"
        searchPlaceholder="Tìm kiếm theo tiêu đề, địa điểm..."
        exportable
        @refresh="loadEvents"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      >
        <template #toolbar-actions>
          <el-button type="primary" @click="showCreateDialog = true">
            <el-icon><Plus /></el-icon>
            Tạo sự kiện mới
          </el-button>
        </template>


      </DataTable>

    <!-- Create/Edit Dialog -->
    <el-dialog
      v-model="showCreateDialog"
      :title="editingEvent ? 'Sửa sự kiện' : 'Tạo sự kiện mới'"
      width="700px"
    >
      <el-form
        ref="eventFormRef"
        :model="eventForm"
        :rules="eventRules"
        label-width="140px"
      >
        <el-form-item label="Tiêu đề" prop="title">
          <el-input v-model="eventForm.title" />
        </el-form-item>
        <el-form-item label="Mô tả" prop="description">
          <el-input
            v-model="eventForm.description"
            type="textarea"
            :rows="4"
          />
        </el-form-item>
        <el-form-item label="Loại sự kiện" prop="type">
          <el-select v-model="eventForm.type" placeholder="Chọn loại sự kiện">
            <el-option label="Họp cư dân" value="meeting" />
            <el-option label="Bảo trì" value="maintenance" />
            <el-option label="Mất điện" value="power_outage" />
            <el-option label="Mất nước" value="water_outage" />
            <el-option label="Sự kiện xã hội" value="social_event" />
            <el-option label="Khẩn cấp" value="emergency" />
          </el-select>
        </el-form-item>
        <el-form-item label="Phạm vi" prop="scope">
          <el-select v-model="eventForm.scope" placeholder="Chọn phạm vi">
            <el-option label="Toàn bộ" value="all" />
            <el-option label="Theo tòa" value="block" />
            <el-option label="Theo tầng" value="floor" />
            <el-option label="Theo căn hộ" value="apartment" />
            <el-option label="Cụ thể" value="specific" />
          </el-select>
        </el-form-item>
        <el-form-item label="Thời gian bắt đầu" prop="start_time">
          <el-date-picker
            v-model="eventForm.start_time"
            type="datetime"
            placeholder="Chọn ngày và giờ bắt đầu"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Thời gian kết thúc" prop="end_time">
          <el-date-picker
            v-model="eventForm.end_time"
            type="datetime"
            placeholder="Chọn ngày và giờ kết thúc"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Địa điểm" prop="location">
          <el-input v-model="eventForm.location" />
        </el-form-item>
        <el-form-item label="Trạng thái" prop="status">
          <el-select v-model="eventForm.status" placeholder="Chọn trạng thái">
            <el-option label="Đã lên lịch" value="scheduled" />
            <el-option label="Đang diễn ra" value="in_progress" />
            <el-option label="Đã hoàn thành" value="completed" />
            <el-option label="Đã hủy" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="Ghi chú" prop="notes">
          <el-input
            v-model="eventForm.notes"
            type="textarea"
            :rows="3"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showCreateDialog = false">Hủy</el-button>
        <el-button type="primary" @click="saveEvent">Lưu</el-button>
      </template>
    </el-dialog>

    <!-- View Event Dialog -->
    <el-dialog
      v-model="showViewDialog"
      title="Chi tiết sự kiện"
      width="700px"
    >
      <div v-if="viewingEvent" class="event-details">
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Tiêu đề:</label>
              <span>{{ viewingEvent.title }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Loại sự kiện:</label>
              <el-tag :type="getEventTypeTag(viewingEvent.type)">
                {{ getEventTypeLabel(viewingEvent.type) }}
              </el-tag>
            </div>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Phạm vi:</label>
              <span>{{ getScopeLabel(viewingEvent.scope) }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Trạng thái:</label>
              <el-tag :type="getStatusTag(viewingEvent.status)">
                {{ getStatusLabel(viewingEvent.status) }}
              </el-tag>
            </div>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Thời gian bắt đầu:</label>
              <span>{{ formatDateTime(viewingEvent.start_time) }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Thời gian kết thúc:</label>
              <span>{{ formatDateTime(viewingEvent.end_time || '') }}</span>
            </div>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="24">
            <div class="detail-item">
              <label>Địa điểm:</label>
              <span>{{ viewingEvent.location || 'Chưa xác định' }}</span>
            </div>
          </el-col>
        </el-row>
        <div class="detail-item full-width">
          <label>Mô tả:</label>
          <p>{{ viewingEvent.description || 'Không có mô tả' }}</p>
        </div>
        <div v-if="viewingEvent.notes" class="detail-item full-width">
          <label>Ghi chú:</label>
          <p>{{ viewingEvent.notes }}</p>
        </div>
      </div>
      <template #footer>
        <el-button @click="showViewDialog = false">Đóng</el-button>
        <el-button type="primary" @click="editEventFromView">Chỉnh sửa</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete, View } from '@element-plus/icons-vue'

import DataTable from '@/components/DataTable.vue'
import api from '@/services/api'
import type { Event } from '@/types'

// Define form interface for type safety
interface EventForm {
  title: string
  description: string
  type: Event['type']
  scope: Event['scope']
  target_scope: Event['target_scope']
  start_time: string
  end_time: string
  location: string
  status: Event['status']
  notes: string
}

// Reactive data
const loading = ref(false)
const events = ref<Event[]>([])
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)
const editingEvent = ref<Event | null>(null)
const eventFormRef = ref()

// Form data
const eventForm = ref<EventForm>({
  title: '',
  description: '',
  type: 'meeting',
  scope: 'all',
  target_scope: null,
  start_time: '',
  end_time: '',
  location: '',
  status: 'scheduled',
  notes: ''
})

// Form validation rules
const eventRules = {
  title: [{ required: true, message: 'Vui lòng nhập tiêu đề', trigger: 'blur' }],
  description: [{ required: true, message: 'Vui lòng nhập mô tả', trigger: 'blur' }],
  type: [{ required: true, message: 'Vui lòng chọn loại sự kiện', trigger: 'change' }],
  scope: [{ required: true, message: 'Vui lòng chọn phạm vi', trigger: 'change' }],
  start_time: [{ required: true, message: 'Vui lòng chọn thời gian bắt đầu', trigger: 'change' }],
  location: [{ required: true, message: 'Vui lòng nhập địa điểm', trigger: 'blur' }]
}

// Table configuration  
const columns = [
  { prop: 'id', label: 'ID', width: 80 },
  { prop: 'title', label: 'Tiêu đề', minWidth: 200 },
  { 
    prop: 'type', 
    label: 'Loại', 
    width: 130,
    formatter: (row: Event) => getEventTypeLabel(row.type)
  },
  { 
    prop: 'scope', 
    label: 'Phạm vi', 
    width: 100,
    formatter: (row: Event) => getScopeLabel(row.scope)
  },
  { 
    prop: 'start_time', 
    label: 'Bắt đầu', 
    width: 160,
    formatter: (row: Event) => formatDateTime(row.start_time) 
  },
  { 
    prop: 'end_time', 
    label: 'Kết thúc', 
    width: 160,
    formatter: (row: Event) => formatDateTime(row.end_time || '') 
  },
  { prop: 'location', label: 'Địa điểm', minWidth: 150 },
  { 
    prop: 'status', 
    label: 'Trạng thái', 
    width: 120,
    formatter: (row: Event) => getStatusLabel(row.status)
  }
]

const filters = [
  {
    key: 'type',
    placeholder: 'Loại sự kiện',
    options: [
      { label: 'Họp cư dân', value: 'meeting' },
      { label: 'Bảo trì', value: 'maintenance' },
      { label: 'Mất điện', value: 'power_outage' },
      { label: 'Mất nước', value: 'water_outage' },
      { label: 'Sự kiện xã hội', value: 'social_event' },
      { label: 'Khẩn cấp', value: 'emergency' }
    ]
  },
  {
    key: 'status',
    placeholder: 'Trạng thái',
    options: [
      { label: 'Đã lên lịch', value: 'scheduled' },
      { label: 'Đang diễn ra', value: 'in_progress' },
      { label: 'Đã hoàn thành', value: 'completed' },
      { label: 'Đã hủy', value: 'cancelled' }
    ]
  }
]

// Forward declarations for actions
const showViewDialog = ref(false)
const viewingEvent = ref<Event | null>(null)

const viewEvent = (event: Event) => {
  viewingEvent.value = event
  showViewDialog.value = true
}

const editEvent = (event: Event) => {
  editingEvent.value = event
  eventForm.value = {
    title: event.title,
    description: event.description || '',
    type: event.type,
    scope: event.scope || 'all',
    target_scope: event.target_scope || null,
    start_time: event.start_time || '',
    end_time: event.end_time || '',
    location: event.location || '',
    status: event.status || 'scheduled',
    notes: event.notes || ''
  }
  showCreateDialog.value = true
}

const editEventFromView = () => {
  if (viewingEvent.value) {
    editEvent(viewingEvent.value)
    showViewDialog.value = false
  }
}

const deleteEvent = async (event: Event) => {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa sự kiện "${event.title}"?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    loading.value = true
    await api.deleteEvent(event.id)
    ElMessage.success('Xóa sự kiện thành công')
    loadEvents()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa sự kiện')
    }
  } finally {
    loading.value = false
  }
}

const actions = [
  {
    key: 'view',
    label: 'Xem',
    type: 'default',
    icon: View,
    handler: viewEvent
  },
  {
    key: 'edit',
    label: 'Sửa',
    type: 'primary',
    icon: Edit,
    handler: editEvent
  },
  {
    key: 'delete',
    label: 'Xóa',
    type: 'danger',
    icon: Delete,
    handler: deleteEvent
  }
]

// Methods
const getEventTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    meeting: 'Họp cư dân',
    maintenance: 'Bảo trì',
    power_outage: 'Mất điện',
    water_outage: 'Mất nước',
    social_event: 'Sự kiện xã hội',
    emergency: 'Khẩn cấp'
  }
  return types[type] || type
}

const getEventTypeTag = (type: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    meeting: 'primary',
    maintenance: 'warning',
    power_outage: 'danger',
    water_outage: 'danger',
    social_event: 'success',
    emergency: 'danger'
  }
  return tags[type] || 'info'
}

const getScopeLabel = (scope: string) => {
  const scopes: Record<string, string> = {
    all: 'Toàn bộ',
    block: 'Theo tòa',
    floor: 'Theo tầng',
    apartment: 'Theo căn hộ',
    specific: 'Cụ thể'
  }
  return scopes[scope] || scope
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    scheduled: 'Đã lên lịch',
    in_progress: 'Đang diễn ra',
    completed: 'Đã hoàn thành',
    cancelled: 'Đã hủy'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    scheduled: 'warning',
    in_progress: 'primary',
    completed: 'success',
    cancelled: 'info'
  }
  return tags[status] || 'info'
}

const loadEvents = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getEvents(params)
    console.log('loadEvents response:', response)
    
    if (response.success) {
      // Handle paginated response
      if (response.data && typeof response.data === 'object' && 'data' in response.data) {
        const paginatedData = response.data as any
        events.value = paginatedData.data as Event[]
        total.value = paginatedData.total || 0
      } else {
        events.value = (response.data as Event[]) || []
        total.value = events.value.length
      }
    } else {
      events.value = []
      total.value = 0
      ElMessage.error(response.message || 'Không thể tải danh sách sự kiện')
    }
  } catch (error: any) {
    console.error('Error loading events:', error)
    events.value = []
    total.value = 0
    ElMessage.error(error.response?.data?.message || 'Lỗi khi tải danh sách sự kiện')
  } finally {
    loading.value = false
  }
}



const formatDateTime = (dateString: string): string => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('vi-VN')
}

const saveEvent = async () => {
  try {
    await eventFormRef.value.validate()
    loading.value = true
    
    // Create a properly typed event data object
    const eventData: Partial<Event> = {
      title: eventForm.value.title,
      description: eventForm.value.description,
      type: eventForm.value.type as Event['type'],
      scope: eventForm.value.scope as Event['scope'],
      target_scope: eventForm.value.target_scope,
      start_time: eventForm.value.start_time,
      end_time: eventForm.value.end_time,
      location: eventForm.value.location,
      status: eventForm.value.status as Event['status'],
      notes: eventForm.value.notes
    }
    
    if (editingEvent.value) {
      await api.updateEvent(editingEvent.value.id, eventData)
      ElMessage.success('Cập nhật sự kiện thành công')
    } else {
      await api.createEvent(eventData)
      ElMessage.success('Tạo sự kiện thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    loadEvents()
  } catch (error: any) {
    ElMessage.error(error.response?.data?.message || 'Lỗi khi lưu sự kiện')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  editingEvent.value = null
  eventForm.value = {
    title: '',
    description: '',
    type: 'meeting',
    scope: 'all',
    target_scope: null,
    start_time: '',
    end_time: '',
    location: '',
    status: 'scheduled',
    notes: ''
  }
  eventFormRef.value?.resetFields()
}

const handleSizeChange = (size: number) => {
  pageSize.value = size
  currentPage.value = 1
  loadEvents()
}

const handleCurrentChange = (page: number) => {
  currentPage.value = page
  loadEvents()
}

// Lifecycle
onMounted(() => {
  loadEvents()
})
</script>

<style scoped>
.events-page {
  height: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  color: #303133;
}

.event-dates {
  font-size: 12px;
}

.date-row {
  margin-bottom: 4px;
}

.date-row:last-child {
  margin-bottom: 0;
}

.participants-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.participants-text {
  font-size: 12px;
  color: #666;
}

.event-details {
  padding: 20px 0;
}

.detail-item {
  margin-bottom: 16px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.detail-item label {
  font-weight: 600;
  min-width: 120px;
  color: #606266;
}

.detail-item span {
  color: #303133;
}

.detail-item.full-width {
  flex-direction: column;
}

.detail-item.full-width label {
  margin-bottom: 8px;
}

.detail-item p {
  margin: 0;
  padding: 8px 0;
  color: #303133;
  line-height: 1.5;
}
</style> 