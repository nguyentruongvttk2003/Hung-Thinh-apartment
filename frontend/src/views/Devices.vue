<template>
  <div class="devices-page">
    <div class="page-header">
      <h2>Quản lý thiết bị</h2>
      <div class="header-actions">
        <el-button type="primary" @click="showCreateDialog = true">
          <el-icon><Plus /></el-icon>
          Thêm thiết bị
        </el-button>
      </div>
    </div>

    <!-- Filters -->
    <el-card class="filter-card">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-select
            v-model="filters.category"
            placeholder="Loại thiết bị"
            clearable
            @change="loadDevices"
          >
            <el-option label="Tất cả" value="" />
            <el-option label="Thang máy" value="elevator" />
            <el-option label="Máy phát điện" value="generator" />
            <el-option label="Máy bơm nước" value="water_pump" />
            <el-option label="Điều hòa" value="air_conditioner" />
            <el-option label="Hệ thống chiếu sáng" value="lighting" />
            <el-option label="An ninh" value="security" />
            <el-option label="Khác" value="other" />
          </el-select>
        </el-col>
        <el-col :span="6">
          <el-select
            v-model="filters.status"
            placeholder="Trạng thái"
            clearable
            @change="loadDevices"
          >
            <el-option label="Tất cả" value="" />
            <el-option label="Hoạt động" value="active" />
            <el-option label="Không hoạt động" value="inactive" />
            <el-option label="Bảo trì" value="maintenance" />
            <el-option label="Hỏng" value="broken" />
          </el-select>
        </el-col>
        <el-col :span="12">
          <el-input
            v-model="filters.search"
            placeholder="Tìm kiếm theo tên, mã thiết bị, vị trí..."
            @input="handleSearch"
            clearable
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </el-col>
      </el-row>
    </el-card>

    <!-- Devices Table -->
    <el-card>
      <el-table
        :data="devices"
        v-loading="loading"
        style="width: 100%"
        @sort-change="handleSortChange"
      >
        <el-table-column prop="device_code" label="Mã thiết bị" width="120" sortable />
        <el-table-column prop="name" label="Tên thiết bị" width="180" sortable />
        <el-table-column prop="category" label="Loại thiết bị" width="150">
          <template #default="{ row }">
            <el-tag :type="getCategoryTagType(row.category)">
              {{ getCategoryLabel(row.category) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="location" label="Vị trí" width="150" sortable />
        <el-table-column prop="brand" label="Thương hiệu" width="120" />
        <el-table-column prop="model" label="Model" width="120" />
        <el-table-column prop="status" label="Trạng thái" width="120">
          <template #default="{ row }">
            <el-tag :type="getStatusTagType(row.status)">
              {{ getStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="installation_date" label="Ngày lắp đặt" width="120" sortable>
          <template #default="{ row }">
            {{ formatDate(row.installation_date) }}
          </template>
        </el-table-column>
        <el-table-column label="Thao tác" width="300" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button size="small" @click="viewDevice(row)">Xem</el-button>
              <el-button size="small" type="primary" @click="editDevice(row)">Sửa</el-button>
              <el-button size="small" type="danger" @click="deleteDevice(row)">Xóa</el-button>
            </div>
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

    <!-- Create/Edit Device Dialog -->
    <el-dialog
      v-model="showCreateDialog"
      :title="editingDevice ? 'Sửa thiết bị' : 'Thêm thiết bị mới'"
      width="700px"
      @close="resetDeviceForm"
    >
      <el-form
        ref="deviceFormRef"
        :model="deviceForm"
        :rules="deviceRules"
        label-width="150px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Tên thiết bị" prop="name">
              <el-input v-model="deviceForm.name" placeholder="Nhập tên thiết bị" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Mã thiết bị" prop="device_code">
              <el-input v-model="deviceForm.device_code" placeholder="Nhập mã thiết bị" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Loại thiết bị" prop="category">
              <el-select v-model="deviceForm.category" placeholder="Chọn loại thiết bị" style="width: 100%">
                <el-option label="Thang máy" value="elevator" />
                <el-option label="Máy phát điện" value="generator" />
                <el-option label="Máy bơm nước" value="water_pump" />
                <el-option label="Điều hòa" value="air_conditioner" />
                <el-option label="Hệ thống chiếu sáng" value="lighting" />
                <el-option label="An ninh" value="security" />
                <el-option label="Khác" value="other" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Vị trí" prop="location">
              <el-input v-model="deviceForm.location" placeholder="Nhập vị trí lắp đặt" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Thương hiệu" prop="brand">
              <el-input v-model="deviceForm.brand" placeholder="Nhập thương hiệu" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Model" prop="model">
              <el-input v-model="deviceForm.model" placeholder="Nhập model" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Ngày lắp đặt" prop="installation_date">
              <el-date-picker
                v-model="deviceForm.installation_date"
                type="date"
                format="DD/MM/YYYY"
                value-format="YYYY-MM-DD"
                placeholder="Chọn ngày lắp đặt"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Trạng thái" prop="status">
              <el-select v-model="deviceForm.status" placeholder="Chọn trạng thái" style="width: 100%">
                <el-option label="Hoạt động" value="active" />
                <el-option label="Không hoạt động" value="inactive" />
                <el-option label="Bảo trì" value="maintenance" />
                <el-option label="Hỏng" value="broken" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Thông số kỹ thuật" prop="specifications">
          <el-input
            v-model="deviceForm.specifications"
            type="textarea"
            :rows="3"
            placeholder="Nhập thông số kỹ thuật"
          />
        </el-form-item>

        <el-form-item label="Ghi chú" prop="notes">
          <el-input
            v-model="deviceForm.notes"
            type="textarea"
            :rows="3"
            placeholder="Nhập ghi chú"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="showCreateDialog = false">Hủy</el-button>
        <el-button type="primary" @click="saveDevice" :loading="saving">
          {{ editingDevice ? 'Cập nhật' : 'Tạo' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- View Device Dialog -->
    <el-dialog
      v-model="showViewDialog"
      title="Chi tiết thiết bị"
      width="700px"
    >
      <div v-if="viewingDevice" class="device-detail">
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Mã thiết bị:</label>
              <p>{{ viewingDevice.device_code }}</p>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Tên thiết bị:</label>
              <p>{{ viewingDevice.name }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Loại thiết bị:</label>
              <el-tag :type="getCategoryTagType(viewingDevice.category)">
                {{ getCategoryLabel(viewingDevice.category) }}
              </el-tag>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Vị trí:</label>
              <p>{{ viewingDevice.location }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Thương hiệu:</label>
              <p>{{ viewingDevice.brand || '-' }}</p>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Model:</label>
              <p>{{ viewingDevice.model || '-' }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Trạng thái:</label>
              <el-tag :type="getStatusTagType(viewingDevice.status)">
                {{ getStatusLabel(viewingDevice.status) }}
              </el-tag>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Ngày lắp đặt:</label>
              <p>{{ formatDate(viewingDevice.installation_date) }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20" v-if="viewingDevice.warranty_expiry">
          <el-col :span="12">
            <div class="detail-item">
              <label>Hết hạn bảo hành:</label>
              <p>{{ formatDate(viewingDevice.warranty_expiry) }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20" v-if="viewingDevice.specifications">
          <el-col :span="24">
            <div class="detail-item">
              <label>Thông số kỹ thuật:</label>
              <p>{{ viewingDevice.specifications }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20" v-if="viewingDevice.notes">
          <el-col :span="24">
            <div class="detail-item">
              <label>Ghi chú:</label>
              <p>{{ viewingDevice.notes }}</p>
            </div>
          </el-col>
        </el-row>
      </div>

      <template #footer>
        <el-button @click="showViewDialog = false">Đóng</el-button>
        <el-button type="primary" @click="editDeviceFromView">Sửa</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox, type FormInstance, type FormRules } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'

import api from '@/services/api'
import type { Device } from '@/types'

// Data
const devices = ref<Device[]>([])
const loading = ref(false)
const saving = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// Dialog states
const showCreateDialog = ref(false)
const showViewDialog = ref(false)
const editingDevice = ref<Device | null>(null)
const viewingDevice = ref<Device | null>(null)
const deviceFormRef = ref<FormInstance>()

// Filters
const filters = reactive({
  category: '',
  status: '',
  search: ''
})

// Form
const deviceForm = reactive({
  name: '',
  device_code: '',
  category: '',
  location: '',
  brand: '',
  model: '',
  installation_date: '',
  status: '',
  specifications: '',
  notes: ''
})

// Form validation rules
const deviceRules: FormRules = {
  name: [{ required: true, message: 'Vui lòng nhập tên thiết bị', trigger: 'blur' }],
  device_code: [{ required: true, message: 'Vui lòng nhập mã thiết bị', trigger: 'blur' }],
  category: [{ required: true, message: 'Vui lòng chọn loại thiết bị', trigger: 'change' }],
  location: [{ required: true, message: 'Vui lòng nhập vị trí', trigger: 'blur' }],
  installation_date: [{ required: true, message: 'Vui lòng chọn ngày lắp đặt', trigger: 'change' }],
  status: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }]
}

// Search timeout
let searchTimeout: number

// Methods
const getCategoryLabel = (category: string) => {
  const categories: Record<string, string> = {
    elevator: 'Thang máy',
    generator: 'Máy phát điện',
    water_pump: 'Máy bơm nước',
    air_conditioner: 'Điều hòa',
    lighting: 'Hệ thống chiếu sáng',
    security: 'An ninh',
    other: 'Khác'
  }
  return categories[category] || category
}

const getCategoryTagType = (category: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    elevator: 'primary',
    generator: 'warning',
    water_pump: 'info',
    air_conditioner: 'success',
    lighting: 'warning',
    security: 'danger',
    other: 'info'
  }
  return tags[category] || 'info'
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    active: 'Hoạt động',
    inactive: 'Không hoạt động',
    maintenance: 'Bảo trì',
    broken: 'Hỏng'
  }
  return statuses[status] || status
}

const getStatusTagType = (status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    active: 'success',
    inactive: 'info',
    maintenance: 'warning',
    broken: 'danger'
  }
  return tags[status] || 'info'
}

const formatDate = (dateString: string): string => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('vi-VN')
}

// Load devices
async function loadDevices() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value,
      category: filters.category || undefined,
      status: filters.status || undefined,
      search: filters.search || undefined
    }
    
    const response = await api.getDevices(params)
    devices.value = response.data
    total.value = response.total
  } catch (error) {
    console.error('Load devices error:', error)
    ElMessage.error('Lỗi khi tải danh sách thiết bị')
  } finally {
    loading.value = false
  }
}

// Handle search
function handleSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    loadDevices()
  }, 300)
}

// Handle sort change
function handleSortChange({ prop, order }: { prop: string; order: string }) {
  // Implement sorting logic if needed
  console.log('Sort change:', prop, order)
}

// View device
function viewDevice(device: Device) {
  viewingDevice.value = device
  showViewDialog.value = true
}

// Edit device
function editDevice(device: Device) {
  editingDevice.value = device
  Object.assign(deviceForm, {
    name: device.name,
    device_code: device.device_code,
    category: device.category,
    location: device.location,
    brand: device.brand || '',
    model: device.model || '',
    installation_date: device.installation_date,
    status: device.status,
    specifications: device.specifications || '',
    notes: device.notes || ''
  })
  showCreateDialog.value = true
}

// Edit device from view
function editDeviceFromView() {
  if (viewingDevice.value) {
    showViewDialog.value = false
    editDevice(viewingDevice.value)
  }
}

// Delete device
async function deleteDevice(device: Device) {
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
    
    await api.deleteDevice(device.id)
    ElMessage.success('Xóa thiết bị thành công')
    loadDevices()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Delete device error:', error)
      ElMessage.error('Có lỗi xảy ra khi xóa thiết bị')
    }
  }
}

// Save device
async function saveDevice() {
  if (!deviceFormRef.value) return
  
  try {
    await deviceFormRef.value.validate()
    saving.value = true

    if (editingDevice.value) {
      await api.updateDevice(editingDevice.value.id, deviceForm as Device)
      ElMessage.success('Cập nhật thiết bị thành công')
    } else {
      await api.createDevice(deviceForm as Device)
      ElMessage.success('Tạo thiết bị thành công')
    }
    
    showCreateDialog.value = false
    resetDeviceForm()
    loadDevices()
  } catch (error: any) {
    console.error('Save device error:', error)
    ElMessage.error('Có lỗi xảy ra khi lưu thiết bị')
  } finally {
    saving.value = false
  }
}

// Reset form
function resetDeviceForm() {
  editingDevice.value = null
  Object.assign(deviceForm, {
    name: '',
    device_code: '',
    category: '',
    location: '',
    brand: '',
    model: '',
    installation_date: '',
    status: '',
    specifications: '',
    notes: ''
  })
  deviceFormRef.value?.resetFields()
}

// Pagination handlers
function handleSizeChange(size: number) {
  pageSize.value = size
  currentPage.value = 1
  loadDevices()
}

function handleCurrentChange(page: number) {
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

.page-header h2 {
  margin: 0;
  color: #303133;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.filter-card {
  margin-bottom: 20px;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: nowrap;
}

.pagination-wrapper {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.device-detail {
  .detail-item {
    margin-bottom: 16px;
  }

  .detail-item label {
    font-weight: 600;
    color: #606266;
    display: block;
    margin-bottom: 4px;
  }

  .detail-item p {
    margin: 0;
    color: #303133;
  }
}
</style>
