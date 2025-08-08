<template>
  <AppLayout>
    <div class="apartments-page">
    <div class="page-header">
      <h2>Quản lý căn hộ</h2>
      <el-button type="primary" @click="showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Thêm căn hộ
      </el-button>
    </div>

    <!-- Filters -->
    <el-card class="filters-card">
      <el-form :model="filters" inline>
        <el-form-item label="Block:">
          <el-select v-model="filters.block" placeholder="Chọn block" clearable>
            <el-option label="Block A" value="A" />
            <el-option label="Block B" value="B" />
            <el-option label="Block C" value="C" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="Trạng thái:">
          <el-select v-model="filters.status" placeholder="Chọn trạng thái" clearable>
            <el-option label="Đã thuê" value="occupied" />
            <el-option label="Trống" value="vacant" />
            <el-option label="Bảo trì" value="maintenance" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="Loại:">
          <el-select v-model="filters.type" placeholder="Chọn loại" clearable>
            <el-option label="Studio" value="studio" />
            <el-option label="1 phòng ngủ" value="1br" />
            <el-option label="2 phòng ngủ" value="2br" />
            <el-option label="3 phòng ngủ" value="3br" />
            <el-option label="4 phòng ngủ" value="4br" />
          </el-select>
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="loadApartments">Lọc</el-button>
          <el-button @click="resetFilters">Làm mới</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Apartments Table -->
    <el-card>
          <el-table
            :data="apartments"
            v-loading="loading"
            style="width: 100%"
          >
            <el-table-column prop="apartment_number" label="Số căn hộ" width="120" />
            <el-table-column prop="block" label="Block" width="80" />
            <el-table-column prop="floor" label="Tầng" width="80" />
            <el-table-column prop="type" label="Loại" width="120">
              <template #default="{ row }">
                <el-tag :type="getTypeTagType(row.type)">
                  {{ getTypeLabel(row.type) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="area" label="Diện tích" width="100">
              <template #default="{ row }">
                {{ row.area }}m²
              </template>
            </el-table-column>
            <el-table-column prop="status" label="Trạng thái" width="120">
              <template #default="{ row }">
                <el-tag :type="getStatusTagType(row.status)">
                  {{ getStatusLabel(row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="owner.name" label="Chủ hộ" />
            <el-table-column label="Thao tác" width="200" fixed="right">
              <template #default="{ row }">
                <el-button size="small" @click="viewApartment(row)">Xem</el-button>
                <el-button size="small" type="primary" @click="editApartment(row)">Sửa</el-button>
                <el-button size="small" type="danger" @click="deleteApartment(row)">Xóa</el-button>
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

        <!-- Create/Edit Dialog -->
        <el-dialog
          v-model="showCreateDialog"
          :title="editingApartment ? 'Sửa căn hộ' : 'Thêm căn hộ'"
          width="600px"
        >
          <el-form
            ref="apartmentFormRef"
            :model="apartmentForm"
            :rules="apartmentRules"
            label-width="120px"
          >
            <el-form-item label="Số căn hộ" prop="apartment_number">
              <el-input v-model="apartmentForm.apartment_number" />
            </el-form-item>
            
            <el-form-item label="Block" prop="block">
              <el-select v-model="apartmentForm.block" placeholder="Chọn block">
                <el-option label="Block A" value="A" />
                <el-option label="Block B" value="B" />
                <el-option label="Block C" value="C" />
              </el-select>
            </el-form-item>
            
            <el-form-item label="Tầng" prop="floor">
              <el-input-number v-model="apartmentForm.floor" :min="1" :max="50" />
            </el-form-item>
            
            <el-form-item label="Loại" prop="type">
              <el-select v-model="apartmentForm.type" placeholder="Chọn loại">
                <el-option label="Studio" value="studio" />
                <el-option label="1 phòng ngủ" value="1br" />
                <el-option label="2 phòng ngủ" value="2br" />
                <el-option label="3 phòng ngủ" value="3br" />
                <el-option label="4 phòng ngủ" value="4br" />
              </el-select>
            </el-form-item>
            
            <el-form-item label="Diện tích" prop="area">
              <el-input-number v-model="apartmentForm.area" :min="20" :max="500" />
              <span style="margin-left: 8px;">m²</span>
            </el-form-item>
            
            <el-form-item label="Trạng thái" prop="status">
              <el-select v-model="apartmentForm.status" placeholder="Chọn trạng thái">
                <el-option label="Đã thuê" value="occupied" />
                <el-option label="Trống" value="vacant" />
                <el-option label="Bảo trì" value="maintenance" />
              </el-select>
            </el-form-item>
          </el-form>
          
          <template #footer>
            <el-button @click="showCreateDialog = false">Hủy</el-button>
            <el-button type="primary" @click="saveApartment" :loading="saving">
              {{ editingApartment ? 'Cập nhật' : 'Thêm' }}
            </el-button>
          </template>
        </el-dialog>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import AppLayout from '@/components/Layout/AppLayout.vue'
import api from '@/services/api'
import type { Apartment } from '@/types'
import { Plus } from '@element-plus/icons-vue'

const router = useRouter()

// Data
const apartments = ref<Apartment[]>([])
const loading = ref(false)
const saving = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)
const editingApartment = ref<Apartment | null>(null)
const apartmentFormRef = ref<FormInstance>()

// Filters
const filters = reactive({
  block: '',
  status: '',
  type: ''
})

// Form interfaces
interface ApartmentForm {
  apartment_number: string
  block: string
  floor: number
  type: 'studio' | '1br' | '2br' | '3br' | '4br'
  area: number
  status: 'occupied' | 'vacant' | 'maintenance'
}

// Form
const apartmentForm = reactive<ApartmentForm>({
  apartment_number: '',
  block: '',
  floor: 1,
  type: 'studio',
  area: 50,
  status: 'vacant'
})

const apartmentRules: FormRules = {
  apartment_number: [
    { required: true, message: 'Vui lòng nhập số căn hộ', trigger: 'blur' }
  ],
  block: [
    { required: true, message: 'Vui lòng chọn block', trigger: 'change' }
  ],
  floor: [
    { required: true, message: 'Vui lòng nhập tầng', trigger: 'blur' }
  ],
  type: [
    { required: true, message: 'Vui lòng chọn loại căn hộ', trigger: 'change' }
  ],
  area: [
    { required: true, message: 'Vui lòng nhập diện tích', trigger: 'blur' }
  ],
  status: [
    { required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }
  ]
}

// Methods
async function loadApartments() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value,
      ...filters
    }
    const response = await api.getApartments(params)
    apartments.value = response.data
    total.value = response.total
  } catch (error) {
    ElMessage.error('Không thể tải danh sách căn hộ')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  Object.assign(filters, {
    block: '',
    status: '',
    type: ''
  })
  loadApartments()
}

function getTypeLabel(type: string): string {
  const typeMap: Record<string, string> = {
    studio: 'Studio',
    '1br': '1 phòng ngủ',
    '2br': '2 phòng ngủ',
    '3br': '3 phòng ngủ',
    '4br': '4 phòng ngủ'
  }
  return typeMap[type] || type
}

function getTypeTagType(type: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const typeTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    studio: 'info',
    '1br': 'success',
    '2br': 'warning',
    '3br': 'danger',
    '4br': 'primary'
  }
  return typeTagMap[type] || 'primary'
}

function getStatusLabel(status: string): string {
  const statusMap: Record<string, string> = {
    occupied: 'Đã thuê',
    vacant: 'Trống',
    maintenance: 'Bảo trì'
  }
  return statusMap[status] || status
}

function getStatusTagType(status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const statusTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    occupied: 'success',
    vacant: 'info',
    maintenance: 'warning'
  }
  return statusTagMap[status] || 'primary'
}

function viewApartment(apartment: Apartment) {
  router.push(`/apartments/${apartment.id}`)
}

function editApartment(apartment: Apartment) {
  editingApartment.value = apartment
  Object.assign(apartmentForm, apartment)
  showCreateDialog.value = true
}

async function deleteApartment(apartment: Apartment) {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa căn hộ ${apartment.apartment_number}?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    await api.deleteApartment(apartment.id)
    ElMessage.success('Xóa căn hộ thành công')
    loadApartments()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Không thể xóa căn hộ')
    }
  }
}

async function saveApartment() {
  if (!apartmentFormRef.value) return
  
  try {
    await apartmentFormRef.value.validate()
    saving.value = true
    
    if (editingApartment.value) {
      await api.updateApartment(editingApartment.value.id, apartmentForm)
      ElMessage.success('Cập nhật căn hộ thành công')
    } else {
      await api.createApartment(apartmentForm)
      ElMessage.success('Thêm căn hộ thành công')
    }
    
    showCreateDialog.value = false
    loadApartments()
  } catch (error: any) {
    ElMessage.error(error.response?.data?.message || 'Có lỗi xảy ra')
  } finally {
    saving.value = false
  }
}

function handleSizeChange(size: number) {
  pageSize.value = size
  currentPage.value = 1
  loadApartments()
}

function handleCurrentChange(page: number) {
  currentPage.value = page
  loadApartments()
}

onMounted(() => {
  loadApartments()
})
</script>

<style scoped>
.apartments-page {
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

.filters-card {
  margin-bottom: 20px;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
</style> 