<template>
    <div class="votes-page">
    <div class="page-header">
      <h1>Quản lý Biểu quyết</h1>
      <el-button type="primary" @click="showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Tạo biểu quyết mới
      </el-button>
    </div>

    <el-card>
      <div class="table-toolbar">
        <div class="filters">
          <el-input
            v-model="searchQuery"
            placeholder="Tìm kiếm biểu quyết..."
            style="width: 300px"
            clearable
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          <el-select v-model="statusFilter" placeholder="Trạng thái" clearable>
            <el-option label="Bản nháp" value="draft" />
            <el-option label="Đang diễn ra" value="active" />
            <el-option label="Đã kết thúc" value="closed" />
            <el-option label="Đã hủy" value="cancelled" />
          </el-select>
          <el-select v-model="typeFilter" placeholder="Loại biểu quyết" clearable>
            <el-option label="Hội nghị chung" value="general_meeting" />
            <el-option label="Phê duyệt ngân sách" value="budget_approval" />
            <el-option label="Bầu chọn ban quản lý" value="management_election" />
            <el-option label="Khác" value="other" />
          </el-select>
        </div>
      </div>

      <el-table :data="filteredVotes" v-loading="loading" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="Tiêu đề" />
        <el-table-column prop="type" label="Loại biểu quyết">
          <template #default="{ row }">
            <el-tag :type="getVoteTypeTag(row.type)">
              {{ getVoteTypeLabel(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="start_time" label="Ngày bắt đầu" />
        <el-table-column prop="end_time" label="Ngày kết thúc" />
        <el-table-column prop="status" label="Trạng thái">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">
              {{ getStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="total_votes" label="Tổng số phiếu" width="120" />
        <el-table-column prop="participation_rate" label="Tỷ lệ tham gia" width="120">
          <template #default="{ row }">
            {{ row.participation_rate }}%
          </template>
        </el-table-column>
        <el-table-column label="Thao tác" width="200">
          <template #default="{ row }">
            <el-button size="small" @click="viewVote(row)">Xem</el-button>
            <el-button size="small" type="primary" @click="editVote(row)">Sửa</el-button>
            <el-button size="small" type="danger" @click="deleteVote(row)">Xóa</el-button>
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
      :title="editingVote ? 'Sửa biểu quyết' : 'Tạo biểu quyết mới'"
      width="800px"
    >
      <el-form
        ref="voteFormRef"
        :model="voteForm"
        :rules="voteRules"
        label-width="140px"
      >
        <el-form-item label="Tiêu đề" prop="title">
          <el-input v-model="voteForm.title" />
        </el-form-item>
        <el-form-item label="Loại biểu quyết" prop="type">
          <el-select v-model="voteForm.type" placeholder="Chọn loại biểu quyết">
            <el-option label="Họp chung cư dân" value="general_meeting" />
            <el-option label="Phê duyệt ngân sách" value="budget_approval" />
            <el-option label="Thay đổi quy định" value="rule_change" />
            <el-option label="Nâng cấp tiện ích" value="facility_upgrade" />
            <el-option label="Khác" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="Thời gian bắt đầu" prop="start_time">
          <el-date-picker
            v-model="voteForm.start_time"
            type="datetime"
            placeholder="Chọn ngày và giờ bắt đầu"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Thời gian kết thúc" prop="end_time">
          <el-date-picker
            v-model="voteForm.end_time"
            type="datetime"
            placeholder="Chọn ngày và giờ kết thúc"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Mô tả" prop="description">
          <el-input
            v-model="voteForm.description"
            type="textarea"
            :rows="4"
          />
        </el-form-item>
        <el-form-item label="Lựa chọn" prop="options">
          <div v-for="(option, index) in voteForm.options" :key="index" class="option-item">
            <el-input v-model="voteForm.options[index]" placeholder="Nhập lựa chọn" />
            <el-button type="danger" @click="removeOption(index)" :disabled="voteForm.options.length <= 2">
              Xóa
            </el-button>
          </div>
          <el-button type="primary" @click="addOption">Thêm lựa chọn</el-button>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showCreateDialog = false">Hủy</el-button>
        <el-button type="primary" @click="saveVote">Lưu</el-button>
      </template>
          </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search } from '@element-plus/icons-vue'

import api from '@/services/api'
import type { Vote } from '@/types'

// Reactive data
const loading = ref(false)
const votes = ref<Vote[]>([])
const searchQuery = ref('')
const statusFilter = ref('')
const typeFilter = ref('')
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)
const editingVote = ref<Vote | null>(null)
const voteFormRef = ref()

// Form data
const voteForm = ref<{
  title: string
  description: string
  type: 'general_meeting' | 'budget_approval' | 'other' | 'rule_change' | 'facility_upgrade'
  scope: 'all' | 'block' | 'floor' | 'apartment'
  target_scope: string | null
  start_time: string
  end_time: string
  status: 'draft' | 'active' | 'closed' | 'cancelled'
  require_quorum: boolean
  quorum_percentage: number
  notes: string
  options: string[]
}>({
  title: '',
  description: '',
  type: 'general_meeting',
  scope: 'all',
  target_scope: null,
  start_time: '',
  end_time: '',
  status: 'draft',
  require_quorum: true,
  quorum_percentage: 50,
  notes: '',
  options: ['', '']
})

// Form validation rules
const voteRules = {
  title: [{ required: true, message: 'Vui lòng nhập tiêu đề', trigger: 'blur' }],
  description: [{ required: true, message: 'Vui lòng nhập mô tả', trigger: 'blur' }],
  type: [{ required: true, message: 'Vui lòng chọn loại biểu quyết', trigger: 'change' }],
  scope: [{ required: true, message: 'Vui lòng chọn phạm vi', trigger: 'change' }],
  start_time: [{ required: true, message: 'Vui lòng chọn thời gian bắt đầu', trigger: 'change' }],
  end_time: [{ required: true, message: 'Vui lòng chọn thời gian kết thúc', trigger: 'change' }]
}

// Computed
const filteredVotes = computed(() => {
  let filtered = votes.value

  if (searchQuery.value) {
    filtered = filtered.filter(vote =>
      vote.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(vote => vote.status === statusFilter.value)
  }

  if (typeFilter.value) {
    filtered = filtered.filter(vote => vote.type === typeFilter.value)
  }

  return filtered
})

// Methods
const getVoteTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    general_meeting: 'Họp chung cư dân',
    budget_approval: 'Phê duyệt ngân sách',
    rule_change: 'Thay đổi quy định',
    facility_upgrade: 'Nâng cấp tiện ích',
    other: 'Khác'
  }
  return types[type] || type
}

const getVoteTypeTag = (type: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    general_meeting: 'primary',
    budget_approval: 'warning',
    rule_change: 'danger',
    facility_upgrade: 'success',
    other: 'info'
  }
  return tags[type] || 'info'
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    draft: 'Bản nháp',
    active: 'Đang diễn ra',
    closed: 'Đã kết thúc',
    cancelled: 'Đã hủy'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' => {
  const tags: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    draft: 'warning',
    active: 'primary',
    closed: 'success',
    cancelled: 'info'
  }
  return tags[status] || 'info'
}

const addOption = () => {
  voteForm.value.options.push('')
}

const removeOption = (index: number) => {
  if (voteForm.value.options.length > 2) {
    voteForm.value.options.splice(index, 1)
  }
}

const fetchVotes = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getVotes(params)
    
    if (response.success) {
      // Handle paginated response
      if (response.data && typeof response.data === 'object' && 'data' in response.data) {
        const paginatedData = response.data as any
        votes.value = paginatedData.data as Vote[]
        total.value = paginatedData.total || 0
      } else {
        votes.value = (response.data as Vote[]) || []
        total.value = votes.value.length
      }
    } else {
      votes.value = []
      total.value = 0
      ElMessage.error(response.message || 'Không thể tải danh sách biểu quyết')
    }
  } catch (error: any) {
    console.error('Error loading votes:', error)
    votes.value = []
    total.value = 0
    ElMessage.error(error.response?.data?.message || 'Lỗi khi tải danh sách biểu quyết')
  } finally {
    loading.value = false
  }
}

const viewVote = async (vote: Vote) => {
  try {
    const response = await api.getVote(vote.id)
    if (response.success) {
      // TODO: Implement view dialog similar to Events.vue
      ElMessage.info(`Xem chi tiết biểu quyết: ${vote.title}`)
    } else {
      ElMessage.error('Không thể tải chi tiết biểu quyết')
    }
  } catch (error) {
    ElMessage.error('Lỗi khi tải chi tiết biểu quyết')
  }
}

const editVote = (vote: Vote) => {
  editingVote.value = vote
  voteForm.value = {
    title: vote.title,
    description: vote.description || '',
    type: vote.type,
    scope: vote.scope || 'all',
    target_scope: vote.target_scope || null,
    start_time: vote.start_time || '',
    end_time: vote.end_time || '',
    status: vote.status || 'draft',
    require_quorum: vote.require_quorum || true,
    quorum_percentage: vote.quorum_percentage || 50,
    notes: vote.notes || '',
    options: Array.isArray(vote.options) ? vote.options.map(opt => typeof opt === 'string' ? opt : opt.option_text) : ['', '']
  }
  showCreateDialog.value = true
}

const deleteVote = async (vote: Vote) => {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa biểu quyết "${vote.title}"?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    const response = await api.deleteVote(vote.id)
    if (response.success) {
      ElMessage.success('Xóa biểu quyết thành công')
      fetchVotes()
    } else {
      ElMessage.error(response.message || 'Lỗi khi xóa biểu quyết')
    }
  } catch (error: any) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa biểu quyết')
    }
  }
}

const saveVote = async () => {
  try {
    await voteFormRef.value.validate()
    
    const formData = {
      ...voteForm.value,
      options: voteForm.value.options.filter(opt => opt.trim()).map(opt => ({ option_text: opt }))
    }
    
    if (editingVote.value) {
      const response = await api.updateVote(editingVote.value.id, formData as Partial<Vote>)
      if (response.success) {
        ElMessage.success('Cập nhật biểu quyết thành công')
      } else {
        ElMessage.error(response.message || 'Lỗi khi cập nhật biểu quyết')
        return
      }
    } else {
      const response = await api.createVote(formData as Partial<Vote>)
      if (response.success) {
        ElMessage.success('Tạo biểu quyết thành công')
      } else {
        ElMessage.error(response.message || 'Lỗi khi tạo biểu quyết')
        return
      }
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchVotes()
  } catch (error: any) {
    ElMessage.error(error.response?.data?.message || 'Lỗi khi lưu biểu quyết')
  }
}

const resetForm = () => {
  editingVote.value = null
  voteForm.value = {
    title: '',
    description: '',
    type: 'general_meeting',
    scope: 'all',
    target_scope: null,
    start_time: '',
    end_time: '',
    status: 'draft',
    require_quorum: true,
    quorum_percentage: 50,
    notes: '',
    options: ['', '']
  }
  voteFormRef.value?.resetFields()
}

const handleSizeChange = (size: number) => {
  pageSize.value = size
  currentPage.value = 1
  fetchVotes()
}

const handleCurrentChange = (page: number) => {
  currentPage.value = page
  fetchVotes()
}

// Lifecycle
onMounted(() => {
  fetchVotes()
})
</script>

<style scoped>
.votes-page {
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

.option-item {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
  align-items: center;
}

.option-item .el-input {
  flex: 1;
}
</style> 