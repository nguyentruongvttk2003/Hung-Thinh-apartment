<template>
    <div class="profile-page">
    <div class="page-header">
      <h1>Thông tin cá nhân</h1>
    </div>

    <div class="profile-content">
      <el-row :gutter="20">
        <el-col :span="8">
          <el-card class="profile-card">
            <div class="profile-avatar">
              <el-avatar :size="120" :src="user.avatar">
                <el-icon><User /></el-icon>
              </el-avatar>
              <h3>{{ user.name }}</h3>
              <p>{{ user.email }}</p>
              <el-tag :type="getRoleTag(user.role)">
                {{ getRoleLabel(user.role) }}
              </el-tag>
            </div>
            <div class="profile-stats">
              <div class="stat-item">
                <div class="stat-number">{{ userStats.total_apartments }}</div>
                <div class="stat-label">Căn hộ quản lý</div>
              </div>
              <div class="stat-item">
                <div class="stat-number">{{ userStats.total_residents }}</div>
                <div class="stat-label">Cư dân</div>
              </div>
            </div>
          </el-card>
        </el-col>

        <el-col :span="16">
          <el-card>
            <template #header>
              <div class="card-header">
                <span>Thông tin chi tiết</span>
                <el-button type="primary" @click="editMode = !editMode">
                  {{ editMode ? 'Hủy' : 'Chỉnh sửa' }}
                </el-button>
              </div>
            </template>

            <el-form
              ref="profileFormRef"
              :model="profileForm"
              :rules="profileRules"
              label-width="120px"
              :disabled="!editMode"
            >
              <el-row :gutter="20">
                <el-col :span="12">
                  <el-form-item label="Họ và tên" prop="name">
                    <el-input v-model="profileForm.name" />
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="Email" prop="email">
                    <el-input v-model="profileForm.email" disabled />
                  </el-form-item>
                </el-col>
              </el-row>

              <el-row :gutter="20">
                <el-col :span="12">
                  <el-form-item label="Số điện thoại" prop="phone">
                    <el-input v-model="profileForm.phone" />
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="Ngày sinh" prop="birth_date">
                    <el-date-picker
                      v-model="profileForm.birth_date"
                      type="date"
                      placeholder="Chọn ngày sinh"
                      format="DD/MM/YYYY"
                      value-format="YYYY-MM-DD"
                    />
                  </el-form-item>
                </el-col>
              </el-row>

              <el-form-item label="Địa chỉ" prop="address">
                <el-input v-model="profileForm.address" />
              </el-form-item>

              <el-form-item label="Mô tả" prop="bio">
                <el-input
                  v-model="profileForm.bio"
                  type="textarea"
                  :rows="3"
                />
              </el-form-item>

              <el-form-item v-if="editMode">
                <el-button type="primary" @click="saveProfile">Lưu thay đổi</el-button>
                <el-button @click="cancelEdit">Hủy</el-button>
              </el-form-item>
            </el-form>
          </el-card>

          <el-card style="margin-top: 20px;">
            <template #header>
              <span>Đổi mật khẩu</span>
            </template>

            <el-form
              ref="passwordFormRef"
              :model="passwordForm"
              :rules="passwordRules"
              label-width="120px"
            >
              <el-form-item label="Mật khẩu cũ" prop="current_password">
                <el-input
                  v-model="passwordForm.current_password"
                  type="password"
                  show-password
                />
              </el-form-item>

              <el-form-item label="Mật khẩu mới" prop="new_password">
                <el-input
                  v-model="passwordForm.new_password"
                  type="password"
                  show-password
                />
              </el-form-item>

              <el-form-item label="Xác nhận mật khẩu" prop="confirm_password">
                <el-input
                  v-model="passwordForm.confirm_password"
                  type="password"
                  show-password
                />
              </el-form-item>

              <el-form-item>
                <el-button type="primary" @click="changePassword">Đổi mật khẩu</el-button>
              </el-form-item>
            </el-form>
          </el-card>

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
      </el-row>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { User, Clock } from '@element-plus/icons-vue'

import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Reactive data
const editMode = ref(false)
const profileFormRef = ref()
const passwordFormRef = ref()

// User data
const user = ref({
  id: 1,
  name: 'Nguyễn Văn A',
  email: 'admin@example.com',
  phone: '0123456789',
  birth_date: '1990-01-01',
  address: 'Tòa A, Căn hộ 101, Chung cư Hưng Thịnh',
  bio: 'Quản lý chung cư Hưng Thịnh',
  role: 'admin',
  avatar: ''
})

const userStats = ref({
  total_apartments: 150,
  total_residents: 450
})

const recentActivities = ref([
  {
    id: 1,
    title: 'Cập nhật thông tin căn hộ A101',
    created_at: '2024-01-15 14:30:00'
  },
  {
    id: 2,
    title: 'Gửi thông báo về bảo trì thang máy',
    created_at: '2024-01-15 10:15:00'
  },
  {
    id: 3,
    title: 'Xử lý phản ánh từ cư dân',
    created_at: '2024-01-14 16:45:00'
  }
])

// Form data
const profileForm = ref({
  name: user.value.name,
  email: user.value.email,
  phone: user.value.phone,
  birth_date: user.value.birth_date,
  address: user.value.address,
  bio: user.value.bio
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

// Form validation rules
const profileRules = {
  name: [{ required: true, message: 'Vui lòng nhập họ và tên', trigger: 'blur' }],
  phone: [{ required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' }],
  address: [{ required: true, message: 'Vui lòng nhập địa chỉ', trigger: 'blur' }]
}

const passwordRules = {
  current_password: [{ required: true, message: 'Vui lòng nhập mật khẩu cũ', trigger: 'blur' }],
  new_password: [
    { required: true, message: 'Vui lòng nhập mật khẩu mới', trigger: 'blur' },
    { min: 6, message: 'Mật khẩu phải có ít nhất 6 ký tự', trigger: 'blur' }
  ],
  confirm_password: [
    { required: true, message: 'Vui lòng xác nhận mật khẩu', trigger: 'blur' },
    {
      validator: (rule: any, value: string, callback: Function) => {
        if (value !== passwordForm.value.new_password) {
          callback(new Error('Mật khẩu xác nhận không khớp'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
}

// Methods
const getRoleLabel = (role: string) => {
  const roles: Record<string, string> = {
    admin: 'Quản trị viên',
    resident: 'Cư dân',
    accountant: 'Kế toán',
    technician: 'Kỹ thuật viên'
  }
  return roles[role] || role
}

const getRoleTag = (role: string) => {
  const tags: Record<string, string> = {
    admin: 'danger',
    resident: 'primary',
    accountant: 'warning',
    technician: 'success'
  }
  return tags[role] || ''
}

const saveProfile = async () => {
  try {
    await profileFormRef.value.validate()
    
    // TODO: Implement API call
    // await api.updateProfile(profileForm.value)
    
    // Update local user data
    Object.assign(user.value, profileForm.value)
    
    ElMessage.success('Cập nhật thông tin thành công')
    editMode.value = false
  } catch (error) {
    ElMessage.error('Lỗi khi cập nhật thông tin')
  }
}

const cancelEdit = () => {
  // Reset form to original values
  profileForm.value = {
    name: user.value.name,
    email: user.value.email,
    phone: user.value.phone,
    birth_date: user.value.birth_date,
    address: user.value.address,
    bio: user.value.bio
  }
  editMode.value = false
}

const changePassword = async () => {
  try {
    await passwordFormRef.value.validate()
    
    // TODO: Implement API call
    // await api.changePassword(passwordForm.value)
    
    ElMessage.success('Đổi mật khẩu thành công')
    passwordForm.value = {
      current_password: '',
      new_password: '',
      confirm_password: ''
    }
    passwordFormRef.value?.resetFields()
  } catch (error) {
    ElMessage.error('Lỗi khi đổi mật khẩu')
  }
}

const fetchUserProfile = async () => {
  try {
    // TODO: Implement API call
    // const response = await api.getProfile()
    // user.value = response.data.user
    // userStats.value = response.data.stats
    // recentActivities.value = response.data.activities
    
    // Update form with user data
    profileForm.value = {
      name: user.value.name,
      email: user.value.email,
      phone: user.value.phone,
      birth_date: user.value.birth_date,
      address: user.value.address,
      bio: user.value.bio
    }
  } catch (error) {
    ElMessage.error('Lỗi khi tải thông tin cá nhân')
  }
}

// Lifecycle
onMounted(() => {
  fetchUserProfile()
})
</script>

<style scoped>
.profile-page {
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  color: #303133;
}

.profile-content {
  max-width: 1200px;
}

.profile-card {
  text-align: center;
}

.profile-avatar {
  margin-bottom: 20px;
}

.profile-avatar h3 {
  margin: 10px 0 5px 0;
  color: #303133;
}

.profile-avatar p {
  margin: 0 0 10px 0;
  color: #909399;
}

.profile-stats {
  display: flex;
  justify-content: space-around;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  color: #409eff;
}

.stat-label {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.activity-list {
  max-height: 300px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  margin-right: 15px;
  color: #409eff;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 500;
  color: #303133;
  margin-bottom: 5px;
}

.activity-time {
  font-size: 12px;
  color: #909399;
}
</style> 