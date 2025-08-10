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
        <el-form-item label="Loại sự kiện" prop="type">
          <el-select v-model="eventForm.type" placeholder="Chọn loại sự kiện">
            <el-option label="Họp cư dân" value="meeting" />
            <el-option label="Sự kiện văn hóa" value="cultural" />
            <el-option label="Bảo trì" value="maintenance" />
            <el-option label="Khác" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="Ngày bắt đầu" prop="start_date">
          <el-date-picker
            v-model="eventForm.start_date"
            type="datetime"
            placeholder="Chọn ngày và giờ bắt đầu"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Ngày kết thúc" prop="end_date">
          <el-date-picker
            v-model="eventForm.end_date"
            type="datetime"
            placeholder="Chọn ngày và giờ kết thúc"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Địa điểm" prop="location">
          <el-input v-model="eventForm.location" />
        </el-form-item>
        <el-form-item label="Số người tối đa" prop="max_participants">
          <el-input-number v-model="eventForm.max_participants" :min="1" />
        </el-form-item>
        <el-form-item label="Mô tả" prop="description">
          <el-input
            v-model="eventForm.description"
            type="textarea"
            :rows="4"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showCreateDialog = false">Hủy</el-button>
        <el-button type="primary" @click="saveEvent">Lưu</el-button>
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
const eventForm = ref({
  title: '',
  type: '',
  start_date: '',
  end_date: '',
  location: '',
  max_participants: 50,
  description: ''
})

// Form validation rules
const eventRules = {
  title: [{ required: true, message: 'Vui lòng nhập tiêu đề', trigger: 'blur' }],
  type: [{ required: true, message: 'Vui lòng chọn loại sự kiện', trigger: 'change' }],
  start_date: [{ required: true, message: 'Vui lòng chọn ngày bắt đầu', trigger: 'change' }],
  end_date: [{ required: true, message: 'Vui lòng chọn ngày kết thúc', trigger: 'change' }],
  location: [{ required: true, message: 'Vui lòng nhập địa điểm', trigger: 'blur' }],
  max_participants: [{ required: true, message: 'Vui lòng nhập số người tối đa', trigger: 'blur' }]
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
    prop: 'start_date', 
    label: 'Bắt đầu', 
    width: 160,
    formatter: (row: Event) => formatDateTime(row.start_date) 
  },
  { 
    prop: 'end_date', 
    label: 'Kết thúc', 
    width: 160,
    formatter: (row: Event) => formatDateTime(row.end_date) 
  },
  { prop: 'location', label: 'Địa điểm', minWidth: 150 },
  { 
    prop: 'status', 
    label: 'Trạng thái', 
    width: 120,
    formatter: (row: Event) => getStatusLabel(row.status)
  },
  { 
    prop: 'current_participants', 
    label: 'Người tham gia', 
    width: 150,
    formatter: (row: Event) => `${row.current_participants}/${row.max_participants || 0}`
  }
]

const filters = [
  {
    key: 'type',
    placeholder: 'Loại sự kiện',
    options: [
      { label: 'Họp cư dân', value: 'meeting' },
      { label: 'Sự kiện văn hóa', value: 'cultural' },
      { label: 'Bảo trì', value: 'maintenance' },
      { label: 'Khác', value: 'other' }
    ]
  },
  {
    key: 'status',
    placeholder: 'Trạng thái',
    options: [
      { label: 'Sắp diễn ra', value: 'upcoming' },
      { label: 'Đang diễn ra', value: 'ongoing' },
      { label: 'Đã kết thúc', value: 'completed' },
      { label: 'Đã hủy', value: 'cancelled' }
    ]
  }
]

// Forward declarations for actions
const viewEvent = (event: Event) => {
  ElMessage.info(`Xem chi tiết sự kiện: ${event.title}`)
}

const editEvent = (event: Event) => {
  editingEvent.value = event
  eventForm.value = {
    title: event.title,
    type: event.type,
    start_date: event.start_date,
    end_date: event.end_date,
    location: event.location,
    max_participants: event.max_participants ?? 50,
    description: event.description || ''
  }
  showCreateDialog.value = true
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
    
    // TODO: Implement when API is ready
    // await api.deleteEvent(event.id)
    ElMessage.success('Xóa sự kiện thành công (demo)')
    loadEvents()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa sự kiện')
    }
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
    cultural: 'Sự kiện văn hóa',
    maintenance: 'Bảo trì',
    other: 'Khác'
  }
  return types[type] || type
}

const getEventTypeTag = (type: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    meeting: 'primary',
    cultural: 'success',
    maintenance: 'warning',
    other: 'info'
  }
  return tags[type] || 'info'
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    upcoming: 'Sắp diễn ra',
    ongoing: 'Đang diễn ra',
    completed: 'Đã kết thúc',
    cancelled: 'Đã hủy'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    upcoming: 'warning',
    ongoing: 'primary',
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
    events.value = response.data
    total.value = response.total
  } catch (error) {
    // Fallback to mock data for demo
    events.value = [
      {
        id: 1,
        title: 'Họp cư dân tháng 1',
        type: 'meeting',
        start_date: '2024-01-25 19:00:00',
        end_date: '2024-01-25 21:00:00',
        location: 'Hội trường tầng 1',
        status: 'upcoming',
        description: 'Họp cư dân định kỳ tháng 1',
        max_participants: 100,
        current_participants: 45,
        created_by: 1,
        created_at: '2024-01-15',
        updated_at: '2024-01-15'
      }
    ]
    total.value = events.value.length
    ElMessage.warning('Sử dụng dữ liệu demo - API chưa available')
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
    
    if (editingEvent.value) {
      // TODO: Implement when API is ready
      // await api.updateEvent(editingEvent.value.id, eventForm.value)
      ElMessage.success('Cập nhật sự kiện thành công (demo)')
    } else {
      // TODO: Implement when API is ready
      // await api.createEvent(eventForm.value)
      ElMessage.success('Tạo sự kiện thành công (demo)')
    }
    
    showCreateDialog.value = false
    resetForm()
    loadEvents()
  } catch (error: any) {
    ElMessage.error(error.response?.data?.message || 'Lỗi khi lưu sự kiện')
  }
}

const resetForm = () => {
  editingEvent.value = null
  eventForm.value = {
    title: '',
    type: '',
    start_date: '',
    end_date: '',
    location: '',
    max_participants: 50,
    description: ''
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
</style> 