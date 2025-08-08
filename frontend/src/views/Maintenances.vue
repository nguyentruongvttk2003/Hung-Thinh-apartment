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
            <el-option label="Chờ xử lý" value="pending" />
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
            <el-tag :type="getPriorityTag(row.priority)">
              {{ getPriorityLabel(row.priority) }}
            </el-tag>
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
            <el-option label="Bảo trì định kỳ" value="scheduled" />
            <el-option label="Sửa chữa" value="repair" />
            <el-option label="Kiểm tra" value="inspection" />
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'
import type { Maintenance } from '@/types'

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
const editingMaintenance = ref<Maintenance | null>(null)
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
      maintenance.device_name.toLowerCase().includes(searchQuery.value.toLowerCase())
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

const getPriorityTag = (priority: string) => {
  const tags: Record<string, string> = {
    low: 'info',
    medium: 'warning',
    high: 'danger',
    urgent: 'danger'
  }
  return tags[priority] || ''
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    pending: 'Chờ xử lý',
    in_progress: 'Đang xử lý',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string) => {
  const tags: Record<string, string> = {
    pending: 'warning',
    in_progress: 'primary',
    completed: 'success',
    cancelled: 'info'
  }
  return tags[status] || ''
}

const fetchMaintenances = async () => {
  loading.value = true
  try {
    // TODO: Implement API call
    // const response = await api.getMaintenances({ page: currentPage.value, per_page: pageSize.value })
    // maintenances.value = response.data.data
    // total.value = response.data.total
    
    // Mock data for now
    maintenances.value = [
      {
        id: 1,
        title: 'Bảo trì thang máy A',
        device_id: 1,
        device_name: 'Thang máy A',
        type: 'scheduled',
        priority: 'medium',
        status: 'in_progress',
        assigned_to: 'Kỹ thuật viên A',
        description: 'Bảo trì định kỳ thang máy',
        scheduled_date: '2024-01-20 09:00:00',
        completed_date: null,
        created_at: '2024-01-15',
        updated_at: '2024-01-15'
      }
    ]
    total.value = maintenances.value.length
  } catch (error) {
    ElMessage.error('Lỗi khi tải danh sách bảo trì')
  } finally {
    loading.value = false
  }
}

const viewMaintenance = (maintenance: Maintenance) => {
  // TODO: Implement view maintenance details
  ElMessage.info(`Xem chi tiết bảo trì: ${maintenance.title}`)
}

const editMaintenance = (maintenance: Maintenance) => {
  editingMaintenance.value = maintenance
  maintenanceForm.value = {
    title: maintenance.title,
    device_id: maintenance.device_id.toString(),
    type: maintenance.type,
    priority: maintenance.priority,
    assigned_to: maintenance.assigned_to,
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
    
    // TODO: Implement API call
    // await api.deleteMaintenance(maintenance.id)
    
    ElMessage.success('Xóa yêu cầu bảo trì thành công')
    fetchMaintenances()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa yêu cầu bảo trì')
    }
  }
}

const saveMaintenance = async () => {
  try {
    await maintenanceFormRef.value.validate()
    
    // TODO: Implement API call
    if (editingMaintenance.value) {
      // await api.updateMaintenance(editingMaintenance.value.id, maintenanceForm.value)
      ElMessage.success('Cập nhật yêu cầu bảo trì thành công')
    } else {
      // await api.createMaintenance(maintenanceForm.value)
      ElMessage.success('Tạo yêu cầu bảo trì thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchMaintenances()
  } catch (error) {
    ElMessage.error('Lỗi khi lưu yêu cầu bảo trì')
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
</style> 