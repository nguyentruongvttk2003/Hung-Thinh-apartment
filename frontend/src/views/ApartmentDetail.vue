<template>
  <AppLayout>
    <div class="apartment-detail-page">
    <div class="page-header">
      <el-button @click="$router.go(-1)" icon="ArrowLeft">Quay lại</el-button>
      <h1>Chi tiết căn hộ {{ apartment?.apartment_number }}</h1>
    </div>

    <div v-if="loading" class="loading-container">
      <el-skeleton :rows="10" animated />
    </div>

    <div v-else-if="apartment" class="apartment-content">
      <el-row :gutter="20">
        <el-col :span="16">
          <!-- Basic Information -->
          <el-card>
            <template #header>
              <span>Thông tin cơ bản</span>
              <el-button type="primary" @click="editMode = !editMode" style="float: right;">
                {{ editMode ? 'Hủy' : 'Chỉnh sửa' }}
              </el-button>
            </template>

            <el-form
              ref="apartmentFormRef"
              :model="apartmentForm"
              :rules="apartmentRules"
              label-width="140px"
              :disabled="!editMode"
            >
              <el-row :gutter="20">
                <el-col :span="12">
                  <el-form-item label="Số căn hộ" prop="apartment_number">
                    <el-input v-model="apartmentForm.apartment_number" />
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="Block" prop="block">
                    <el-input v-model="apartmentForm.block" />
                  </el-form-item>
                </el-col>
              </el-row>

              <el-row :gutter="20">
                <el-col :span="12">
                  <el-form-item label="Tầng" prop="floor">
                    <el-input-number v-model="apartmentForm.floor" :min="1" />
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="Diện tích" prop="area">
                    <el-input-number v-model="apartmentForm.area" :min="0" :precision="2" />
                    <span style="margin-left: 8px;">m²</span>
                  </el-form-item>
                </el-col>
              </el-row>

              <el-row :gutter="20">
                <el-col :span="12">
                  <el-form-item label="Loại căn hộ" prop="type">
                    <el-select v-model="apartmentForm.type" placeholder="Chọn loại căn hộ">
                      <el-option label="1 phòng ngủ" value="1BR" />
                      <el-option label="2 phòng ngủ" value="2BR" />
                      <el-option label="3 phòng ngủ" value="3BR" />
                      <el-option label="Duplex" value="duplex" />
                    </el-select>
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="Trạng thái" prop="status">
                    <el-select v-model="apartmentForm.status" placeholder="Chọn trạng thái">
                      <el-option label="Đang cho thuê" value="rented" />
                      <el-option label="Trống" value="vacant" />
                      <el-option label="Bảo trì" value="maintenance" />
                      <el-option label="Đã bán" value="sold" />
                    </el-select>
                  </el-form-item>
                </el-col>
              </el-row>

              <el-form-item label="Mô tả" prop="description">
                <el-input
                  v-model="apartmentForm.description"
                  type="textarea"
                  :rows="3"
                />
              </el-form-item>

              <el-form-item v-if="editMode">
                <el-button type="primary" @click="saveApartment">Lưu thay đổi</el-button>
                <el-button @click="cancelEdit">Hủy</el-button>
              </el-form-item>
            </el-form>
          </el-card>

          <!-- Residents -->
          <el-card style="margin-top: 20px;">
            <template #header>
              <span>Cư dân</span>
              <el-button type="primary" @click="showAddResidentDialog = true" style="float: right;">
                Thêm cư dân
              </el-button>
            </template>

            <el-table :data="residents" stripe>
              <el-table-column prop="name" label="Họ và tên" />
              <el-table-column prop="phone" label="Số điện thoại" />
              <el-table-column prop="email" label="Email" />
              <el-table-column prop="relationship" label="Quan hệ" />
              <el-table-column prop="is_owner" label="Chủ hộ">
                <template #default="{ row }">
                  <el-tag :type="row.is_owner ? 'success' : 'info'">
                    {{ row.is_owner ? 'Chủ hộ' : 'Thành viên' }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column label="Thao tác" width="150">
                <template #default="{ row }">
                  <el-button size="small" @click="editResident(row)">Sửa</el-button>
                  <el-button size="small" type="danger" @click="deleteResident(row)">Xóa</el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-card>

          <!-- Recent Activities -->
          <el-card style="margin-top: 20px;">
            <template #header>
              <span>Hoạt động gần đây</span>
            </template>

            <div class="activity-list">
              <div
                v-for="activity in recentActivities"
                :key="activity.id"
                class="activity-item"
              >
                <div class="activity-icon">
                  <el-icon><Clock /></el-icon>
                </div>
                <div class="activity-content">
                  <div class="activity-title">{{ activity.title }}</div>
                  <div class="activity-time">{{ activity.created_at }}</div>
                </div>
              </div>
            </div>
          </el-card>
        </el-col>

        <el-col :span="8">
          <!-- Quick Stats -->
          <el-card>
            <template #header>
              <span>Thống kê nhanh</span>
            </template>

            <div class="stats-grid">
              <div class="stat-item">
                <div class="stat-icon">
                  <el-icon><User /></el-icon>
                </div>
                <div class="stat-content">
                  <div class="stat-number">{{ stats.total_residents }}</div>
                  <div class="stat-label">Cư dân</div>
                </div>
              </div>

              <div class="stat-item">
                <div class="stat-icon">
                  <el-icon><Document /></el-icon>
                </div>
                <div class="stat-content">
                  <div class="stat-number">{{ stats.total_invoices }}</div>
                  <div class="stat-label">Hóa đơn</div>
                </div>
              </div>

              <div class="stat-item">
                <div class="stat-icon">
                  <el-icon><Money /></el-icon>
                </div>
                <div class="stat-content">
                  <div class="stat-number">{{ stats.total_payments }}</div>
                  <div class="stat-label">Thanh toán</div>
                </div>
              </div>

              <div class="stat-item">
                <div class="stat-icon">
                  <el-icon><Warning /></el-icon>
                </div>
                <div class="stat-content">
                  <div class="stat-number">{{ stats.pending_feedbacks }}</div>
                  <div class="stat-label">Phản ánh chờ xử lý</div>
                </div>
              </div>
            </div>
          </el-card>

          <!-- Quick Actions -->
          <el-card style="margin-top: 20px;">
            <template #header>
              <span>Thao tác nhanh</span>
            </template>

            <div class="quick-actions">
              <el-button type="primary" block @click="createInvoice">
                <el-icon><Document /></el-icon>
                Tạo hóa đơn
              </el-button>
              <el-button type="success" block @click="sendNotification">
                <el-icon><Bell /></el-icon>
                Gửi thông báo
              </el-button>
              <el-button type="warning" block @click="scheduleMaintenance">
                <el-icon><Tools /></el-icon>
                Lên lịch bảo trì
              </el-button>
              <el-button type="info" block @click="viewHistory">
                <el-icon><Clock /></el-icon>
                Xem lịch sử
              </el-button>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <div v-else-if="!loading && !apartment" class="error-container">
      <el-empty description="Không tìm thấy thông tin căn hộ" />
    </div>

    <!-- Add/Edit Resident Dialog -->
    <el-dialog
      v-model="showAddResidentDialog"
      :title="editingResident ? 'Sửa thông tin cư dân' : 'Thêm cư dân mới'"
      width="500px"
    >
      <el-form
        ref="residentFormRef"
        :model="residentForm"
        :rules="residentRules"
        label-width="100px"
      >
        <el-form-item label="Họ và tên" prop="name">
          <el-input v-model="residentForm.name" />
        </el-form-item>
        <el-form-item label="Số điện thoại" prop="phone">
          <el-input v-model="residentForm.phone" />
        </el-form-item>
        <el-form-item label="Email" prop="email">
          <el-input v-model="residentForm.email" />
        </el-form-item>
        <el-form-item label="Quan hệ" prop="relationship">
          <el-select v-model="residentForm.relationship" placeholder="Chọn quan hệ">
            <el-option label="Chủ hộ" value="owner" />
            <el-option label="Vợ/Chồng" value="spouse" />
            <el-option label="Con" value="child" />
            <el-option label="Bố/Mẹ" value="parent" />
            <el-option label="Khác" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="Chủ hộ" prop="is_owner">
          <el-switch v-model="residentForm.is_owner" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showAddResidentDialog = false">Hủy</el-button>
        <el-button type="primary" @click="saveResident">Lưu</el-button>
      </template>
    </el-dialog>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { ArrowLeft, User, Document, Money, Warning, Clock, Bell, Tools } from '@element-plus/icons-vue'
import AppLayout from '@/components/Layout/AppLayout.vue'
import type { Apartment, Resident } from '@/types'

const route = useRoute()
const router = useRouter()

// Reactive data
const loading = ref(true)
const editMode = ref(false)
const showAddResidentDialog = ref(false)
const editingResident = ref<Resident | null>(null)
const apartmentFormRef = ref()
const residentFormRef = ref()

// Define activity type for recentActivities
interface Activity {
  id: number
  title: string
  created_at: string
}

// Data
const apartment = ref<Apartment | null>(null)
const residents = ref<Resident[]>([])
const recentActivities = ref<Activity[]>([])
const stats = ref({
  total_residents: 0,
  total_invoices: 0,
  total_payments: 0,
  pending_feedbacks: 0
})

// Form data
const apartmentForm = ref({
  apartment_number: '',
  block: '',
  floor: 1,
  area: 0,
  type: '',
  status: '',
  description: ''
})

const residentForm = ref({
  name: '',
  phone: '',
  email: '',
  relationship: '',
  is_owner: false
})

// Form validation rules
const apartmentRules = {
  apartment_number: [{ required: true, message: 'Vui lòng nhập số căn hộ', trigger: 'blur' }],
  block: [{ required: true, message: 'Vui lòng nhập block', trigger: 'blur' }],
  floor: [{ required: true, message: 'Vui lòng nhập tầng', trigger: 'blur' }],
  area: [{ required: true, message: 'Vui lòng nhập diện tích', trigger: 'blur' }],
  type: [{ required: true, message: 'Vui lòng chọn loại căn hộ', trigger: 'change' }],
  status: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }]
}

const residentRules = {
  name: [{ required: true, message: 'Vui lòng nhập họ và tên', trigger: 'blur' }],
  phone: [{ required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' }],
  email: [{ required: true, message: 'Vui lòng nhập email', trigger: 'blur' }],
  relationship: [{ required: true, message: 'Vui lòng chọn quan hệ', trigger: 'change' }]
}

// Methods
const fetchApartmentDetail = async () => {
  loading.value = true
  try {
    const apartmentId = route.params.id
    
    // TODO: Implement API call
    // const response = await api.getApartmentDetail(apartmentId)
    // apartment.value = response.data.apartment
    // residents.value = response.data.residents
    // stats.value = response.data.stats
    // recentActivities.value = response.data.activities
    
    // Mock data for now
    apartment.value = {
      id: 1,
      apartment_number: 'A101',
      block: 'A',
      floor: 1,
      area: 85.5,
      type: '2br',
      status: 'occupied',
      description: 'Căn hộ 2 phòng ngủ, view đẹp',
      owner_id: 1,
      created_at: '2024-01-01',
      updated_at: '2024-01-15'
    }
    
    residents.value = [
      {
        id: 1,
        name: 'Nguyễn Văn A',
        phone: '0123456789',
        email: 'nguyenvana@example.com',
        relationship: 'owner',
        is_owner: true,
        apartment_id: 1,
        created_at: '2024-01-01',
        updated_at: '2024-01-01'
      }
    ]
    
    stats.value = {
      total_residents: 3,
      total_invoices: 12,
      total_payments: 10,
      pending_feedbacks: 1
    }
    
    recentActivities.value = [
      {
        id: 1,
        title: 'Thanh toán hóa đơn tháng 1',
        created_at: '2024-01-20 10:30:00'
      },
      {
        id: 2,
        title: 'Thêm cư dân mới',
        created_at: '2024-01-18 14:15:00'
      }
    ]
    
    // Update form - apartment.value is guaranteed to not be null here
    if (apartment.value) {
      apartmentForm.value = {
        apartment_number: apartment.value.apartment_number,
        block: apartment.value.block,
        floor: apartment.value.floor,
        area: apartment.value.area,
        type: apartment.value.type,
        status: apartment.value.status,
        description: apartment.value.description || ''
      }
    }
  } catch (error) {
    ElMessage.error('Lỗi khi tải thông tin căn hộ')
  } finally {
    loading.value = false
  }
}

const saveApartment = async () => {
  try {
    await apartmentFormRef.value.validate()
    
    // TODO: Implement API call
    // await api.updateApartment(apartment.value.id, apartmentForm.value)
    
    // Update local data
    if (apartment.value) {
      Object.assign(apartment.value, apartmentForm.value)
    }
    
    ElMessage.success('Cập nhật thông tin căn hộ thành công')
    editMode.value = false
  } catch (error) {
    ElMessage.error('Lỗi khi cập nhật thông tin căn hộ')
  }
}

const cancelEdit = () => {
  // Reset form to original values
  if (apartment.value) {
    apartmentForm.value = {
      apartment_number: apartment.value.apartment_number,
      block: apartment.value.block,
      floor: apartment.value.floor,
      area: apartment.value.area,
      type: apartment.value.type,
      status: apartment.value.status,
      description: apartment.value.description || ''
    }
  }
  editMode.value = false
}

const editResident = (resident: Resident) => {
  editingResident.value = resident
  residentForm.value = {
    name: resident.name,
    phone: resident.phone,
    email: resident.email,
    relationship: resident.relationship,
    is_owner: resident.is_owner
  }
  showAddResidentDialog.value = true
}

const deleteResident = async (resident: Resident) => {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa cư dân "${resident.name}"?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    // TODO: Implement API call
    // await api.deleteResident(resident.id)
    
    ElMessage.success('Xóa cư dân thành công')
    fetchApartmentDetail()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa cư dân')
    }
  }
}

const saveResident = async () => {
  try {
    await residentFormRef.value.validate()
    
    // TODO: Implement API call
    if (editingResident.value) {
      // await api.updateResident(editingResident.value.id, residentForm.value)
      ElMessage.success('Cập nhật thông tin cư dân thành công')
    } else {
      // await api.createResident({ ...residentForm.value, apartment_id: apartment.value.id })
      ElMessage.success('Thêm cư dân thành công')
    }
    
    showAddResidentDialog.value = false
    resetResidentForm()
    fetchApartmentDetail()
  } catch (error) {
    ElMessage.error('Lỗi khi lưu thông tin cư dân')
  }
}

const resetResidentForm = () => {
  editingResident.value = null
  residentForm.value = {
    name: '',
    phone: '',
    email: '',
    relationship: '',
    is_owner: false
  }
  residentFormRef.value?.resetFields()
}

const createInvoice = () => {
  // TODO: Navigate to invoice creation page
  ElMessage.info('Chuyển đến trang tạo hóa đơn')
}

const sendNotification = () => {
  // TODO: Open notification dialog
  ElMessage.info('Mở dialog gửi thông báo')
}

const scheduleMaintenance = () => {
  // TODO: Open maintenance scheduling dialog
  ElMessage.info('Mở dialog lên lịch bảo trì')
}

const viewHistory = () => {
  // TODO: Navigate to history page
  ElMessage.info('Chuyển đến trang lịch sử')
}

// Lifecycle
onMounted(() => {
  fetchApartmentDetail()
})
</script>

<style scoped>
.apartment-detail-page {
  padding: 20px;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  color: #303133;
}

.loading-container {
  padding: 20px;
}

.apartment-content {
  max-width: 1400px;
}

.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.stat-icon {
  margin-right: 12px;
  font-size: 24px;
  color: #409eff;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  color: #303133;
}

.stat-label {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}

.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-list {
  max-height: 300px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  margin-right: 12px;
  color: #409eff;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 500;
  color: #303133;
  margin-bottom: 4px;
}

.activity-time {
  font-size: 12px;
  color: #909399;
}
</style> 