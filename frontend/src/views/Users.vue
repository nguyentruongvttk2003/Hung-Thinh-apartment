<template>
    <div class="users-page">
    <div class="page-header">
      <h2>Quản lý người dùng</h2>
      <el-button type="primary" @click="resetForm(); showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Thêm người dùng
      </el-button>
    </div>

      <!-- Users Table -->
      <el-card>
        <el-table
          :data="users"
          v-loading="loading"
          style="width: 100%"
        >
          <el-table-column prop="name" label="Họ tên" />
          <el-table-column prop="email" label="Email" />
          <el-table-column prop="phone" label="Số điện thoại" />
          <el-table-column prop="role" label="Vai trò" width="120">
            <template #default="{ row }">
              <el-tag :type="getRoleTagType(row.role)">
                {{ getRoleLabel(row.role) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="status" label="Trạng thái" width="120">
            <template #default="{ row }">
              <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
                {{ row.status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Thao tác" width="200" fixed="right">
            <template #default="{ row }">
              <el-button size="small" type="primary" @click="editUser(row)">Sửa</el-button>
              <el-button size="small" type="danger" @click="deleteUser(row)">Xóa</el-button>
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
        :title="editingUser ? 'Sửa người dùng' : 'Thêm người dùng'"
        width="500px"
      >
        <el-form
          ref="userFormRef"
          :model="userForm"
          :rules="userRules"
          label-width="100px"
        >
          <el-form-item label="Họ tên" prop="name">
            <el-input v-model="userForm.name" />
          </el-form-item>
          
          <el-form-item label="Email" prop="email">
            <el-input v-model="userForm.email" type="email" />
          </el-form-item>
          
          <el-form-item label="Số điện thoại" prop="phone">
            <el-input v-model="userForm.phone" />
          </el-form-item>
          
          <el-form-item label="Vai trò" prop="role">
            <el-select v-model="userForm.role" placeholder="Chọn vai trò">
              <el-option label="Admin" value="admin" />
              <el-option label="Cư dân" value="resident" />
              <el-option label="Kỹ thuật viên" value="technician" />
              <el-option label="Kế toán" value="accountant" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Trạng thái" prop="status">
            <el-select v-model="userForm.status" placeholder="Chọn trạng thái">
              <el-option label="Hoạt động" value="active" />
              <el-option label="Không hoạt động" value="inactive" />
            </el-select>
          </el-form-item>
          
          <el-form-item v-if="!editingUser" label="Mật khẩu" prop="password">
            <el-input v-model="userForm.password" type="password" show-password />
          </el-form-item>
        </el-form>
        
        <template #footer>
          <el-button @click="showCreateDialog = false">Hủy</el-button>
          <el-button type="primary" @click="saveUser" :loading="saving">
            {{ editingUser ? 'Cập nhật' : 'Thêm' }}
          </el-button>
        </template>
      </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'

import api from '@/services/api'
import type { User } from '@/types'
import { Plus } from '@element-plus/icons-vue'

// Data
const users = ref<User[]>([])
const loading = ref(false)
const saving = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)
const editingUser = ref<User | null>(null)
const userFormRef = ref<FormInstance>()

// Form interfaces
interface UserForm {
  name: string
  email: string
  phone: string
  role: 'admin' | 'resident' | 'technician' | 'accountant'
  status: 'active' | 'inactive'
  password: string
}

// Form
const userForm = reactive<UserForm>({
  name: '',
  email: '',
  phone: '',
  role: 'resident',
  status: 'active',
  password: ''
})

const userRules: FormRules = {
  name: [
    { required: true, message: 'Vui lòng nhập họ tên', trigger: 'blur' }
  ],
  email: [
    { required: true, message: 'Vui lòng nhập email', trigger: 'blur' },
    { type: 'email', message: 'Email không hợp lệ', trigger: 'blur' }
  ],
  phone: [
    { required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' }
  ],
  role: [
    { required: true, message: 'Vui lòng chọn vai trò', trigger: 'change' }
  ],
  status: [
    { required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }
  ],
  password: [
    { required: true, message: 'Vui lòng nhập mật khẩu', trigger: 'blur' },
    { min: 6, message: 'Mật khẩu phải có ít nhất 6 ký tự', trigger: 'blur' }
  ]
}

// Methods
async function loadUsers() {
  loading.value = true
  try {
    console.log('Loading users with params:', {
      page: currentPage.value,
      per_page: pageSize.value
    })
    
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getUsers(params)
    console.log('Users API response:', response)
    console.log('Response type:', typeof response)
    console.log('Response keys:', Object.keys(response))
    
    users.value = response.data || []
    total.value = response.total || 0
    console.log('Set users:', users.value.length, 'total:', total.value)
  } catch (error: any) {
    console.error('Users load error:', error)
    console.error('Error response:', error.response?.data)
    ElMessage.error('Không thể tải danh sách người dùng: ' + (error.message || 'Unknown error'))
  } finally {
    loading.value = false
  }
}

function getRoleLabel(role: string): string {
  const roleMap: Record<string, string> = {
    admin: 'Admin',
    resident: 'Cư dân',
    technician: 'Kỹ thuật viên',
    accountant: 'Kế toán'
  }
  return roleMap[role] || role
}

function getRoleTagType(role: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const roleTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    admin: 'danger',
    resident: 'success',
    technician: 'warning',
    accountant: 'info'
  }
  return roleTagMap[role] || 'primary'
}

function editUser(user: User) {
  editingUser.value = user
  Object.assign(userForm, user)
  showCreateDialog.value = true
}

async function deleteUser(user: User) {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa người dùng ${user.name}?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    await api.deleteUser(user.id)
    ElMessage.success('Xóa người dùng thành công')
    loadUsers()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Không thể xóa người dùng')
    }
  }
}

async function saveUser() {
  if (!userFormRef.value) return
  
  try {
    console.log('Validating user form...')
    await userFormRef.value.validate()
    saving.value = true
    
    console.log('User form data:', userForm)
    
    if (editingUser.value) {
      console.log('Updating user:', editingUser.value.id)
      await api.updateUser(editingUser.value.id, userForm)
      ElMessage.success('Cập nhật người dùng thành công')
    } else {
      console.log('Creating new user...')
      const result = await api.createUser(userForm)
      console.log('Create user result:', result)
      ElMessage.success('Thêm người dùng thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    loadUsers()
  } catch (error: any) {
    console.error('Save user error:', error)
    console.error('Error response:', error.response?.data)
    
    let errorMessage = 'Có lỗi xảy ra'
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0]
      errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
    } else if (error.message) {
      errorMessage = error.message
    }
    
    ElMessage.error(errorMessage)
  } finally {
    saving.value = false
  }
}

function handleSizeChange(size: number) {
  pageSize.value = size
  currentPage.value = 1
  loadUsers()
}

function handleCurrentChange(page: number) {
  currentPage.value = page
  loadUsers()
}

function resetForm() {
  editingUser.value = null
  Object.assign(userForm, {
    name: '',
    email: '',
    phone: '',
    role: 'resident',
    status: 'active',
    password: ''
  })
  userFormRef.value?.resetFields()
}

onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
.users-page {
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

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
</style> 