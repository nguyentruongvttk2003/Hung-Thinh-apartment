<template>
    <div class="maintenances-page">
    <div class="page-header">
      <h1>Quản lý Bảo trì</h1>
      <el-button type="primary" @click="showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Tạo yêu cầu bảo trì
      </el-button>
    </div>

    <el-card>
      <div class="table-toolbar">
        <div class="filters">
          <el-input
            v-model="searchQuery"
            placeholder="Tìm kiếm bảo trì..."
            style="width: 300px"
            clearable
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          <el-select v-model="statusFilter" placeholder="Trạng thái" clearable>
            <el-option label="Đã lên lịch" value="scheduled" />
            <el-option label="Đang xử lý" value="in_progress" />
            <el-option label="Hoàn thành" value="completed" />
            <el-option label="Đã hủy" value="cancelled" />
          </el-select>
          <el-select v-model="priorityFilter" placeholder="Độ ưu tiên" clearable>
            <el-option label="Thấp" value="low" />
            <el-option label="Trung bình" value="medium" />
            <el-option label="Cao" value="high" />
            <el-option label="Khẩn cấp" value="urgent" />
          </el-select>
        </div>
      </div>

      <el-table :data="filteredMaintenances" v-loading="loading" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="Tiêu đề" />
        <el-table-column prop="device_name" label="Thiết bị" />
        <el-table-column prop="priority" label="Độ ưu tiên">
          <template #default="{ row }">
            <el-tag v-if="row.priority" :type="getPriorityTag(row.priority)">
              {{ getPriorityLabel(row.priority) }}
            </el-tag>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="Trạng thái">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">
              {{ getStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="assigned_to" label="Người phụ trách" />
        <el-table-column prop="created_at" label="Ngày tạo" />
        <el-table-column prop="scheduled_date" label="Ngày dự kiến" />
        <el-table-column label="Thao tác" width="200">
          <template #default="{ row }">
            <el-button size="small" @click="viewMaintenance(row)">Xem</el-button>
            <el-button size="small" type="primary" @click="editMaintenance(row)">Sửa</el-button>
            <el-button size="small" type="danger" @click="deleteMaintenance(row)">Xóa</el-button>
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
      :title="editingMaintenance ? 'Sửa yêu cầu bảo trì' : 'Tạo yêu cầu bảo trì mới'"
      width="700px"
    >
      <el-form
        ref="maintenanceFormRef"
        :model="maintenanceForm"
        :rules="maintenanceRules"
        label-width="140px"
      >
        <el-form-item label="Tiêu đề" prop="title">
          <el-input v-model="maintenanceForm.title" />
        </el-form-item>
        <el-form-item label="Thiết bị" prop="device_id">
          <el-select v-model="maintenanceForm.device_id" placeholder="Chọn thiết bị">
            <el-option label="Thang máy A" value="1" />
            <el-option label="Hệ thống điện" value="2" />
            <el-option label="Hệ thống nước" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="Loại bảo trì" prop="type">
          <el-select v-model="maintenanceForm.type" placeholder="Chọn loại bảo trì">
            <el-option label="Bảo trì định kỳ" value="preventive" />
            <el-option label="Bảo trì sửa chữa" value="corrective" />
            <el-option label="Bảo trì khẩn cấp" value="emergency" />
          </el-select>
        </el-form-item>
        <el-form-item label="Độ ưu tiên" prop="priority">
          <el-select v-model="maintenanceForm.priority" placeholder="Chọn độ ưu tiên">
            <el-option label="Thấp" value="low" />
            <el-option label="Trung bình" value="medium" />
            <el-option label="Cao" value="high" />
            <el-option label="Khẩn cấp" value="urgent" />
          </el-select>
        </el-form-item>
        <el-form-item label="Người phụ trách" prop="assigned_to">
          <el-select v-model="maintenanceForm.assigned_to" placeholder="Chọn người phụ trách">
            <el-option label="Kỹ thuật viên A" value="1" />
            <el-option label="Kỹ thuật viên B" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="Ngày dự kiến" prop="scheduled_date">
          <el-date-picker
            v-model="maintenanceForm.scheduled_date"
            type="datetime"
            placeholder="Chọn ngày và giờ"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Mô tả" prop="description">
          <el-input
            v-model="maintenanceForm.description"
            type="textarea"
            :rows="4"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showCreateDialog = false">Hủy</el-button>
        <el-button type="primary" @click="saveMaintenance">Lưu</el-button>
      </template>
    </el-dialog>

    <!-- View Details Dialog -->
    <el-dialog
      v-model="showViewDialog"
      title="Chi tiết yêu cầu bảo trì"
      width="700px"
    >
      <div v-if="viewingMaintenance" class="maintenance-details">
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Tiêu đề:</label>
              <span>{{ viewingMaintenance.title }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Thiết bị:</label>
              <span>{{ viewingMaintenance.device_name || 'N/A' }}</span>
            </div>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Loại bảo trì:</label>
              <span>{{ getTypeLabel(viewingMaintenance.type) }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Độ ưu tiên:</label>
              <el-tag v-if="viewingMaintenance.priority" :type="getPriorityTag(viewingMaintenance.priority)">
                {{ getPriorityLabel(viewingMaintenance.priority) }}
              </el-tag>
              <span v-else>Chưa định</span>
            </div>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Trạng thái:</label>
              <el-tag :type="getStatusTag(viewingMaintenance.status)">
                {{ getStatusLabel(viewingMaintenance.status) }}
              </el-tag>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Người phụ trách:</label>
              <span>{{ viewingMaintenance.technician_name || viewingMaintenance.assigned_to || 'Chưa phân công' }}</span>
            </div>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Ngày tạo:</label>
              <span>{{ formatDate(viewingMaintenance.created_at) }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Ngày dự kiến:</label>
              <span>{{ formatDate(viewingMaintenance.scheduled_date) }}</span>
            </div>
          </el-col>
        </el-row>
        <div class="detail-item full-width">
          <label>Mô tả:</label>
          <p>{{ viewingMaintenance.description || 'Không có mô tả' }}</p>
        </div>
      </div>
      <template #footer>
        <el-button @click="showViewDialog = false">Đóng</el-button>
        <el-button type="primary" @click="editMaintenanceFromView">Chỉnh sửa</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'

import type { Maintenance } from '@/types'
import api from '@/services/api'

// Reactive data
const loading = ref(false)
const maintenances = ref<Maintenance[]>([])
const searchQuery = ref('')
const statusFilter = ref('')
const priorityFilter = ref('')
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)
const showViewDialog = ref(false)
const editingMaintenance = ref<Maintenance | null>(null)
const viewingMaintenance = ref<Maintenance | null>(null)
const maintenanceFormRef = ref()

// Form data
const maintenanceForm = ref({
  title: '',
  device_id: '',
  type: '',
  priority: '',
  assigned_to: '',
  scheduled_date: '',
  description: ''
})

// Form validation rules
const maintenanceRules = {
  title: [{ required: true, message: 'Vui lòng nhập tiêu đề', trigger: 'blur' }],
  device_id: [{ required: true, message: 'Vui lòng chọn thiết bị', trigger: 'change' }],
  type: [{ required: true, message: 'Vui lòng chọn loại bảo trì', trigger: 'change' }],
  priority: [{ required: true, message: 'Vui lòng chọn độ ưu tiên', trigger: 'change' }],
  assigned_to: [{ required: true, message: 'Vui lòng chọn người phụ trách', trigger: 'change' }],
  scheduled_date: [{ required: true, message: 'Vui lòng chọn ngày dự kiến', trigger: 'change' }]
}

// Computed
const filteredMaintenances = computed(() => {
  let filtered = maintenances.value

  if (searchQuery.value) {
    filtered = filtered.filter(maintenance =>
      maintenance.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (maintenance.device_name && maintenance.device_name.toLowerCase().includes(searchQuery.value.toLowerCase()))
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(maintenance => maintenance.status === statusFilter.value)
  }

  if (priorityFilter.value) {
    filtered = filtered.filter(maintenance => maintenance.priority === priorityFilter.value)
  }

  return filtered
})

// Methods
const getPriorityLabel = (priority: string) => {
  const priorities: Record<string, string> = {
    low: 'Thấp',
    medium: 'Trung bình',
    high: 'Cao',
    urgent: 'Khẩn cấp'
  }
  return priorities[priority] || priority
}

const getPriorityTag = (priority: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    low: 'info',
    medium: 'warning',
    high: 'danger',
    urgent: 'danger'
  }
  return tags[priority] || 'info'
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    scheduled: 'Đã lên lịch',
    in_progress: 'Đang xử lý',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    scheduled: 'info',
    in_progress: 'primary',
    completed: 'success',
    cancelled: 'danger'
  }
  return tags[status] || 'info'
}

const fetchMaintenances = async () => {
  console.log('fetchMaintenances called')
  loading.value = true
  try {
    const params = { 
      page: currentPage.value, 
      per_page: pageSize.value,
      search: searchQuery.value,
      status: statusFilter.value,
      priority: priorityFilter.value
    }
    console.log('Fetching maintenances with params:', params)
    
    const response = await api.getMaintenances(params)
    console.log('Fetch maintenances response:', response)
    
    maintenances.value = response.data
    total.value = response.total
    
    console.log('Updated maintenances:', maintenances.value.length, 'items')
  } catch (error) {
    ElMessage.error('Lỗi khi tải danh sách bảo trì')
    console.error('Fetch maintenances error:', error)
  } finally {
    loading.value = false
  }
}

const viewMaintenance = async (maintenance: Maintenance) => {
  try {
    loading.value = true
    const response = await api.getMaintenance(maintenance.id)
    console.log('View maintenance response:', response)
    // Extract maintenance data from the new response format
    viewingMaintenance.value = response.data
    showViewDialog.value = true
  } catch (error) {
    ElMessage.error('Lỗi khi tải chi tiết bảo trì')
    console.error('View maintenance error:', error)
    // Fallback to use current data
    viewingMaintenance.value = maintenance
    showViewDialog.value = true
  } finally {
    loading.value = false
  }
}

const editMaintenance = (maintenance: Maintenance) => {
  editingMaintenance.value = maintenance
  maintenanceForm.value = {
    title: maintenance.title,
    device_id: maintenance.device_id.toString(),
    type: maintenance.type,
    priority: maintenance.priority || '',
    assigned_to: maintenance.assigned_to || '',
    scheduled_date: maintenance.scheduled_date,
    description: maintenance.description || ''
  }
  showCreateDialog.value = true
}

const deleteMaintenance = async (maintenance: Maintenance) => {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa yêu cầu bảo trì "${maintenance.title}"?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    loading.value = true
    await api.deleteMaintenance(maintenance.id)
    
    ElMessage.success('Xóa yêu cầu bảo trì thành công')
    fetchMaintenances()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa yêu cầu bảo trì')
    }
  } finally {
    loading.value = false
  }
}

const saveMaintenance = async () => {
  try {
    await maintenanceFormRef.value.validate()
    loading.value = true
    
    const formData = {
      ...maintenanceForm.value,
      device_id: parseInt(maintenanceForm.value.device_id),
      type: maintenanceForm.value.type as 'preventive' | 'corrective' | 'emergency',
      priority: maintenanceForm.value.priority as 'low' | 'medium' | 'high' | 'urgent' | undefined
    }
    
    console.log('Saving maintenance:', formData)
    
    if (editingMaintenance.value) {
      await api.updateMaintenance(editingMaintenance.value.id, formData)
      ElMessage.success('Cập nhật yêu cầu bảo trì thành công')
    } else {
      const result = await api.createMaintenance(formData)
      console.log('Create maintenance result:', result)
      ElMessage.success('Tạo yêu cầu bảo trì thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    console.log('About to fetch maintenances after save...')
    await fetchMaintenances()
    console.log('Fetched maintenances after save')
  } catch (error) {
    console.error('Save maintenance error:', error)
    ElMessage.error('Lỗi khi lưu yêu cầu bảo trì')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  editingMaintenance.value = null
  maintenanceForm.value = {
    title: '',
    device_id: '',
    type: '',
    priority: '',
    assigned_to: '',
    scheduled_date: '',
    description: ''
  }
  maintenanceFormRef.value?.resetFields()
}

const handleSizeChange = (size: number) => {
  pageSize.value = size
  currentPage.value = 1
  fetchMaintenances()
}

const handleCurrentChange = (page: number) => {
  currentPage.value = page
  fetchMaintenances()
}

// Additional functions
const editMaintenanceFromView = () => {
  if (viewingMaintenance.value) {
    editMaintenance(viewingMaintenance.value)
    showViewDialog.value = false
  }
}

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('vi-VN')
}

const getTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    preventive: 'Bảo trì định kỳ',
    corrective: 'Bảo trì sửa chữa',
    emergency: 'Bảo trì khẩn cấp'
  }
  return types[type] || type
}

// Lifecycle
onMounted(() => {
  fetchMaintenances()
})
</script>

<style scoped>
.maintenances-page {
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

.maintenance-details {
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