<template>
  <AppLayout>
    <div class="devices-page">
      <div class="page-header">
        <h1>Quản lý Thiết bị</h1>
      </div>

      <DataTable
        title="Danh sách thiết bị"
        :data="devices"
        :columns="columns"
        :loading="loading"
        :total="total"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :filters="filters"
        :actions="actions"
        :search-keys="['name', 'location', 'description']"
        searchPlaceholder="Tìm kiếm theo tên, vị trí..."
        exportable
        @refresh="loadDevices"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      >
        <template #toolbar-actions>
          <el-button type="primary" @click="showCreateDialog = true">
            <el-icon><Plus /></el-icon>
            Thêm thiết bị
          </el-button>
        </template>
      </DataTable>

      <!-- Create/Edit Dialog -->
    <el-dialog
      v-model="showCreateDialog"
      :title="editingDevice ? 'Sửa thiết bị' : 'Thêm thiết bị mới'"
      width="600px"
    >
      <el-form
        ref="deviceFormRef"
        :model="deviceForm"
        :rules="deviceRules"
        label-width="120px"
      >
        <el-form-item label="Tên thiết bị" prop="name">
          <el-input v-model="deviceForm.name" />
        </el-form-item>
        <el-form-item label="Loại thiết bị" prop="type">
          <el-select v-model="deviceForm.type" placeholder="Chọn loại thiết bị">
            <el-option label="Thang máy" value="elevator" />
            <el-option label="Hệ thống điện" value="electrical" />
            <el-option label="Hệ thống nước" value="water" />
            <el-option label="Camera" value="camera" />
            <el-option label="Khác" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="Vị trí" prop="location">
          <el-input v-model="deviceForm.location" />
        </el-form-item>
        <el-form-item label="Trạng thái" prop="status">
          <el-select v-model="deviceForm.status" placeholder="Chọn trạng thái">
            <el-option label="Hoạt động" value="active" />
            <el-option label="Bảo trì" value="maintenance" />
            <el-option label="Hỏng" value="broken" />
          </el-select>
        </el-form-item>
        <el-form-item label="Mô tả" prop="description">
          <el-input
            v-model="deviceForm.description"
            type="textarea"
            :rows="3"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showCreateDialog = false">Hủy</el-button>
        <el-button type="primary" @click="saveDevice">Lưu</el-button>
      </template>
    </el-dialog>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete, View } from '@element-plus/icons-vue'
import AppLayout from '@/components/Layout/AppLayout.vue'
import DataTable from '@/components/DataTable.vue'
import api from '@/services/api'
import type { Device } from '@/types'

// Reactive data
const loading = ref(false)
const devices = ref<Device[]>([])
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)
const editingDevice = ref<Device | null>(null)
const deviceFormRef = ref()

// Form data
const deviceForm = ref({
  name: '',
  type: '',
  location: '',
  status: '',
  description: ''
})

// Form validation rules
const deviceRules = {
  name: [{ required: true, message: 'Vui lòng nhập tên thiết bị', trigger: 'blur' }],
  type: [{ required: true, message: 'Vui lòng chọn loại thiết bị', trigger: 'change' }],
  location: [{ required: true, message: 'Vui lòng nhập vị trí', trigger: 'blur' }],
  status: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }]
}

// Forward declarations for handlers
const viewDevice = (device: Device) => {
  ElMessage.info(`Xem chi tiết thiết bị: ${device.name}`)
}

const editDevice = (device: Device) => {
  editingDevice.value = device
  deviceForm.value = {
    name: device.name,
    type: device.type,
    location: device.location,
    status: device.status,
    description: device.description || ''
  }
  showCreateDialog.value = true
}

const deleteDevice = async (device: Device) => {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa thiết bị "${device.name}"?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    // Mock delete for demo - replace with real API call when available
    ElMessage.success('Xóa thiết bị thành công')
    loadDevices()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa thiết bị')
    }
  }
}

// Table configuration
const columns = [
  { prop: 'id', label: 'ID', width: 80 },
  { prop: 'name', label: 'Tên thiết bị', minWidth: 150 },
  { 
    prop: 'type', 
    label: 'Loại thiết bị', 
    width: 150,
    formatter: (row: Device) => getDeviceTypeLabel(row.type)
  },
  { prop: 'location', label: 'Vị trí', minWidth: 120 },
  { 
    prop: 'status', 
    label: 'Trạng thái', 
    width: 120,
    formatter: (row: Device) => getStatusLabel(row.status)
  },
  { 
    prop: 'last_maintenance', 
    label: 'Bảo trì cuối', 
    width: 120,
    formatter: (row: Device) => formatDate(row.last_maintenance)
  },
  { 
    prop: 'next_maintenance', 
    label: 'Bảo trì tiếp theo', 
    width: 120,
    formatter: (row: Device) => formatDate(row.next_maintenance)
  }
]

const filters = [
  {
    key: 'status',
    placeholder: 'Trạng thái',
    options: [
      { label: 'Hoạt động', value: 'active' },
      { label: 'Bảo trì', value: 'maintenance' },
      { label: 'Hỏng', value: 'broken' }
    ]
  },
  {
    key: 'type',
    placeholder: 'Loại thiết bị',
    options: [
      { label: 'Thang máy', value: 'elevator' },
      { label: 'Hệ thống điện', value: 'electrical' },
      { label: 'Hệ thống nước', value: 'water' },
      { label: 'Camera', value: 'camera' },
      { label: 'Khác', value: 'other' }
    ]
  }
]

const actions = [
  {
    key: 'view',
    label: 'Xem',
    type: 'default',
    icon: View,
    handler: viewDevice
  },
  {
    key: 'edit',
    label: 'Sửa',
    type: 'primary',
    icon: Edit,
    handler: editDevice
  },
  {
    key: 'delete',
    label: 'Xóa',
    type: 'danger',
    icon: Delete,
    handler: deleteDevice
  }
]

// Methods
const getDeviceTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    elevator: 'Thang máy',
    electrical: 'Hệ thống điện',
    water: 'Hệ thống nước',
    camera: 'Camera',
    other: 'Khác'
  }
  return types[type] || type
}

const getDeviceTypeTag = (type: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    elevator: 'primary',
    electrical: 'warning',
    water: 'info',
    camera: 'success',
    other: 'info'
  }
  return tags[type] || 'info'
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    active: 'Hoạt động',
    maintenance: 'Bảo trì',
    broken: 'Hỏng'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    active: 'success',
    maintenance: 'warning',
    broken: 'danger'
  }
  return tags[status] || 'info'
}

const loadDevices = async () => {
  loading.value = true
  try {
    // Mock data for demo - replace with real API call when available
    const mockDevices: Device[] = [
      {
        id: 1,
        name: 'Thang máy A',
        type: 'elevator',
        location: 'Tòa A',
        status: 'active',
        description: 'Thang máy tòa A',
        last_maintenance: '2024-01-15',
        next_maintenance: '2024-04-15',
        created_at: '2024-01-01',
        updated_at: '2024-01-15'
      },
      {
        id: 2,
        name: 'Camera sảnh',
        type: 'camera',
        location: 'Sảnh tầng 1',
        status: 'active',
        description: 'Camera an ninh sảnh chính',
        last_maintenance: '2024-01-10',
        next_maintenance: '2024-04-10',
        created_at: '2024-01-01',
        updated_at: '2024-01-10'
      }
    ]
    
    devices.value = mockDevices
    total.value = mockDevices.length
  } catch (error) {
    ElMessage.error('Lỗi khi tải danh sách thiết bị')
  } finally {
    loading.value = false
  }
}



const formatDate = (dateString: string): string => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('vi-VN')
}

const saveDevice = async () => {
  try {
    await deviceFormRef.value.validate()
    
    if (editingDevice.value) {
      // Mock update for demo - replace with real API call when available
      ElMessage.success('Cập nhật thiết bị thành công')
    } else {
      // Mock create for demo - replace with real API call when available
      ElMessage.success('Thêm thiết bị thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    loadDevices()
  } catch (error: any) {
    ElMessage.error('Lỗi khi lưu thiết bị')
  }
}

const resetForm = () => {
  editingDevice.value = null
  deviceForm.value = {
    name: '',
    type: '',
    location: '',
    status: '',
    description: ''
  }
  deviceFormRef.value?.resetFields()
}

const handleSizeChange = (size: number) => {
  pageSize.value = size
  currentPage.value = 1
  loadDevices()
}

const handleCurrentChange = (page: number) => {
  currentPage.value = page
  loadDevices()
}

// Lifecycle
onMounted(() => {
  loadDevices()
})
</script>

<style scoped>
.devices-page {
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

.maintenance-info {
  font-size: 12px;
}

.maintenance-date {
  margin-bottom: 4px;
}

.maintenance-date:last-child {
  margin-bottom: 0;
}
</style> 