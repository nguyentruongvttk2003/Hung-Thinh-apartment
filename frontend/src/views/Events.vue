<template>
  <div class="events-page">
    <div class="page-header">
      <h1>Quản lý Sự kiện</h1>
      <el-button type="primary" @click="showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Tạo sự kiện mới
      </el-button>
    </div>

    <el-card>
      <div class="table-toolbar">
        <div class="filters">
          <el-input
            v-model="searchQuery"
            placeholder="Tìm kiếm sự kiện..."
            style="width: 300px"
            clearable
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          <el-select v-model="typeFilter" placeholder="Loại sự kiện" clearable>
            <el-option label="Họp cư dân" value="meeting" />
            <el-option label="Sự kiện văn hóa" value="cultural" />
            <el-option label="Bảo trì" value="maintenance" />
            <el-option label="Khác" value="other" />
          </el-select>
          <el-select v-model="statusFilter" placeholder="Trạng thái" clearable>
            <el-option label="Sắp diễn ra" value="upcoming" />
            <el-option label="Đang diễn ra" value="ongoing" />
            <el-option label="Đã kết thúc" value="completed" />
            <el-option label="Đã hủy" value="cancelled" />
          </el-select>
        </div>
      </div>

      <el-table :data="filteredEvents" v-loading="loading" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="Tiêu đề" />
        <el-table-column prop="type" label="Loại sự kiện">
          <template #default="{ row }">
            <el-tag :type="getEventTypeTag(row.type)">
              {{ getEventTypeLabel(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="start_date" label="Ngày bắt đầu" />
        <el-table-column prop="end_date" label="Ngày kết thúc" />
        <el-table-column prop="location" label="Địa điểm" />
        <el-table-column prop="status" label="Trạng thái">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">
              {{ getStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="participants_count" label="Số người tham gia" width="120" />
        <el-table-column label="Thao tác" width="200">
          <template #default="{ row }">
            <el-button size="small" @click="viewEvent(row)">Xem</el-button>
            <el-button size="small" type="primary" @click="editEvent(row)">Sửa</el-button>
            <el-button size="small" type="danger" @click="deleteEvent(row)">Xóa</el-button>
          </template>
        </el-table-column>
      </el-table>

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
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'
import type { Event } from '@/types'

// Reactive data
const loading = ref(false)
const events = ref<Event[]>([])
const searchQuery = ref('')
const typeFilter = ref('')
const statusFilter = ref('')
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

// Computed
const filteredEvents = computed(() => {
  let filtered = events.value

  if (searchQuery.value) {
    filtered = filtered.filter(event =>
      event.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      event.location.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (typeFilter.value) {
    filtered = filtered.filter(event => event.type === typeFilter.value)
  }

  if (statusFilter.value) {
    filtered = filtered.filter(event => event.status === statusFilter.value)
  }

  return filtered
})

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

const getEventTypeTag = (type: string) => {
  const tags: Record<string, string> = {
    meeting: 'primary',
    cultural: 'success',
    maintenance: 'warning',
    other: 'info'
  }
  return tags[type] || ''
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

const getStatusTag = (status: string) => {
  const tags: Record<string, string> = {
    upcoming: 'warning',
    ongoing: 'primary',
    completed: 'success',
    cancelled: 'info'
  }
  return tags[status] || ''
}

const fetchEvents = async () => {
  loading.value = true
  try {
    // TODO: Implement API call
    // const response = await api.getEvents({ page: currentPage.value, per_page: pageSize.value })
    // events.value = response.data.data
    // total.value = response.data.total
    
    // Mock data for now
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
        participants_count: 45,
        created_at: '2024-01-15',
        updated_at: '2024-01-15'
      }
    ]
    total.value = events.value.length
  } catch (error) {
    ElMessage.error('Lỗi khi tải danh sách sự kiện')
  } finally {
    loading.value = false
  }
}

const viewEvent = (event: Event) => {
  // TODO: Implement view event details
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
    max_participants: event.max_participants,
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
    
    // TODO: Implement API call
    // await api.deleteEvent(event.id)
    
    ElMessage.success('Xóa sự kiện thành công')
    fetchEvents()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa sự kiện')
    }
  }
}

const saveEvent = async () => {
  try {
    await eventFormRef.value.validate()
    
    // TODO: Implement API call
    if (editingEvent.value) {
      // await api.updateEvent(editingEvent.value.id, eventForm.value)
      ElMessage.success('Cập nhật sự kiện thành công')
    } else {
      // await api.createEvent(eventForm.value)
      ElMessage.success('Tạo sự kiện thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchEvents()
  } catch (error) {
    ElMessage.error('Lỗi khi lưu sự kiện')
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
  fetchEvents()
}

const handleCurrentChange = (page: number) => {
  currentPage.value = page
  fetchEvents()
}

// Lifecycle
onMounted(() => {
  fetchEvents()
})
</script>

<style scoped>
.events-page {
  padding: 20px;
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

.table-toolbar {
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  align-items: center;
}

.pagination-wrapper {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}
</style> 