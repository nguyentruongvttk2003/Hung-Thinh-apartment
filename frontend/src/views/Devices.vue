<template>
  <div class="devices-page">
    <div class="page-header">
      <h1>Quản lý Thiết bị</h1>
      <el-button type="primary" @click="showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Thêm thiết bị
      </el-button>
    </div>

    <el-card>
      <div class="table-toolbar">
        <div class="filters">
          <el-input
            v-model="searchQuery"
            placeholder="Tìm kiếm thiết bị..."
            style="width: 300px"
            clearable
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          <el-select v-model="statusFilter" placeholder="Trạng thái" clearable>
            <el-option label="Hoạt động" value="active" />
            <el-option label="Bảo trì" value="maintenance" />
            <el-option label="Hỏng" value="broken" />
          </el-select>
          <el-select v-model="typeFilter" placeholder="Loại thiết bị" clearable>
            <el-option label="Thang máy" value="elevator" />
            <el-option label="Hệ thống điện" value="electrical" />
            <el-option label="Hệ thống nước" value="water" />
            <el-option label="Camera" value="camera" />
            <el-option label="Khác" value="other" />
          </el-select>
        </div>
      </div>

      <el-table :data="filteredDevices" v-loading="loading" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="Tên thiết bị" />
        <el-table-column prop="type" label="Loại thiết bị">
          <template #default="{ row }">
            <el-tag :type="getDeviceTypeTag(row.type)">
              {{ getDeviceTypeLabel(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="location" label="Vị trí" />
        <el-table-column prop="status" label="Trạng thái">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">
              {{ getStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="last_maintenance" label="Bảo trì cuối" />
        <el-table-column prop="next_maintenance" label="Bảo trì tiếp theo" />
        <el-table-column label="Thao tác" width="200">
          <template #default="{ row }">
            <el-button size="small" @click="viewDevice(row)">Xem</el-button>
            <el-button size="small" type="primary" @click="editDevice(row)">Sửa</el-button>
            <el-button size="small" type="danger" @click="deleteDevice(row)">Xóa</el-button>
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
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'
import type { Device } from '@/types'

// Reactive data
const loading = ref(false)
const devices = ref<Device[]>([])
const searchQuery = ref('')
const statusFilter = ref('')
const typeFilter = ref('')
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

// Computed
const filteredDevices = computed(() => {
  let filtered = devices.value

  if (searchQuery.value) {
    filtered = filtered.filter(device =>
      device.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      device.location.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(device => device.status === statusFilter.value)
  }

  if (typeFilter.value) {
    filtered = filtered.filter(device => device.type === typeFilter.value)
  }

  return filtered
})

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

const getDeviceTypeTag = (type: string) => {
  const tags: Record<string, string> = {
    elevator: 'primary',
    electrical: 'warning',
    water: 'info',
    camera: 'success',
    other: ''
  }
  return tags[type] || ''
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    active: 'Hoạt động',
    maintenance: 'Bảo trì',
    broken: 'Hỏng'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string) => {
  const tags: Record<string, string> = {
    active: 'success',
    maintenance: 'warning',
    broken: 'danger'
  }
  return tags[status] || ''
}

const fetchDevices = async () => {
  loading.value = true
  try {
    // TODO: Implement API call
    // const response = await api.getDevices({ page: currentPage.value, per_page: pageSize.value })
    // devices.value = response.data.data
    // total.value = response.data.total
    
    // Mock data for now
    devices.value = [
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
      }
    ]
    total.value = devices.value.length
  } catch (error) {
    ElMessage.error('Lỗi khi tải danh sách thiết bị')
  } finally {
    loading.value = false
  }
}

const viewDevice = (device: Device) => {
  // TODO: Implement view device details
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
    
    // TODO: Implement API call
    // await api.deleteDevice(device.id)
    
    ElMessage.success('Xóa thiết bị thành công')
    fetchDevices()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa thiết bị')
    }
  }
}

const saveDevice = async () => {
  try {
    await deviceFormRef.value.validate()
    
    // TODO: Implement API call
    if (editingDevice.value) {
      // await api.updateDevice(editingDevice.value.id, deviceForm.value)
      ElMessage.success('Cập nhật thiết bị thành công')
    } else {
      // await api.createDevice(deviceForm.value)
      ElMessage.success('Thêm thiết bị thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchDevices()
  } catch (error) {
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
  fetchDevices()
}

const handleCurrentChange = (page: number) => {
  currentPage.value = page
  fetchDevices()
}

// Lifecycle
onMounted(() => {
  fetchDevices()
})
</script>

<style scoped>
.devices-page {
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