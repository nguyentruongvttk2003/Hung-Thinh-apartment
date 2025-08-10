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
            <el-option label="Đang diễn ra" value="active" />
            <el-option label="Đã kết thúc" value="completed" />
            <el-option label="Đã hủy" value="cancelled" />
          </el-select>
          <el-select v-model="typeFilter" placeholder="Loại biểu quyết" clearable>
            <el-option label="Quyết định chung" value="decision" />
            <el-option label="Lựa chọn" value="choice" />
            <el-option label="Đánh giá" value="rating" />
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
        <el-table-column prop="start_date" label="Ngày bắt đầu" />
        <el-table-column prop="end_date" label="Ngày kết thúc" />
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
            <el-option label="Quyết định chung" value="decision" />
            <el-option label="Lựa chọn" value="choice" />
            <el-option label="Đánh giá" value="rating" />
          </el-select>
        </el-form-item>
        <el-form-item label="Ngày bắt đầu" prop="start_date">
          <el-date-picker
            v-model="voteForm.start_date"
            type="datetime"
            placeholder="Chọn ngày và giờ bắt đầu"
            format="DD/MM/YYYY HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="Ngày kết thúc" prop="end_date">
          <el-date-picker
            v-model="voteForm.end_date"
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
const voteForm = ref({
  title: '',
  type: '',
  start_date: '',
  end_date: '',
  description: '',
  options: ['', '']
})

// Form validation rules
const voteRules = {
  title: [{ required: true, message: 'Vui lòng nhập tiêu đề', trigger: 'blur' }],
  type: [{ required: true, message: 'Vui lòng chọn loại biểu quyết', trigger: 'change' }],
  start_date: [{ required: true, message: 'Vui lòng chọn ngày bắt đầu', trigger: 'change' }],
  end_date: [{ required: true, message: 'Vui lòng chọn ngày kết thúc', trigger: 'change' }],
  description: [{ required: true, message: 'Vui lòng nhập mô tả', trigger: 'blur' }]
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
    decision: 'Quyết định chung',
    choice: 'Lựa chọn',
    rating: 'Đánh giá'
  }
  return types[type] || type
}

const getVoteTypeTag = (type: string) => {
  const tags: Record<string, string> = {
    decision: 'primary',
    choice: 'success',
    rating: 'warning'
  }
  return tags[type] || ''
}

const getStatusLabel = (status: string) => {
  const statuses: Record<string, string> = {
    active: 'Đang diễn ra',
    completed: 'Đã kết thúc',
    cancelled: 'Đã hủy'
  }
  return statuses[status] || status
}

const getStatusTag = (status: string) => {
  const tags: Record<string, string> = {
    active: 'primary',
    completed: 'success',
    cancelled: 'info'
  }
  return tags[status] || ''
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
    // TODO: Implement API call
    // const response = await api.getVotes({ page: currentPage.value, per_page: pageSize.value })
    // votes.value = response.data.data
    // total.value = response.data.total
    
    // Mock data for now
    votes.value = [
      {
        id: 1,
        title: 'Biểu quyết về việc nâng cấp hệ thống thang máy',
        type: 'decision',
        start_date: '2024-01-20 00:00:00',
        end_date: '2024-01-25 23:59:59',
        status: 'active',
        description: 'Biểu quyết về việc nâng cấp hệ thống thang máy tòa A',
        total_votes: 45,
        participation_rate: 75,
        created_at: '2024-01-15',
        updated_at: '2024-01-15'
      }
    ]
    total.value = votes.value.length
  } catch (error) {
    ElMessage.error('Lỗi khi tải danh sách biểu quyết')
  } finally {
    loading.value = false
  }
}

const viewVote = (vote: Vote) => {
  // TODO: Implement view vote details
  ElMessage.info(`Xem chi tiết biểu quyết: ${vote.title}`)
}

const editVote = (vote: Vote) => {
  editingVote.value = vote
  voteForm.value = {
    title: vote.title,
    type: vote.type,
    start_date: vote.start_date,
    end_date: vote.end_date,
    description: vote.description || '',
    options: ['', ''] // TODO: Load actual options
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
    
    // TODO: Implement API call
    // await api.deleteVote(vote.id)
    
    ElMessage.success('Xóa biểu quyết thành công')
    fetchVotes()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Lỗi khi xóa biểu quyết')
    }
  }
}

const saveVote = async () => {
  try {
    await voteFormRef.value.validate()
    
    // TODO: Implement API call
    if (editingVote.value) {
      // await api.updateVote(editingVote.value.id, voteForm.value)
      ElMessage.success('Cập nhật biểu quyết thành công')
    } else {
      // await api.createVote(voteForm.value)
      ElMessage.success('Tạo biểu quyết thành công')
    }
    
    showCreateDialog.value = false
    resetForm()
    fetchVotes()
  } catch (error) {
    ElMessage.error('Lỗi khi lưu biểu quyết')
  }
}

const resetForm = () => {
  editingVote.value = null
  voteForm.value = {
    title: '',
    type: '',
    start_date: '',
    end_date: '',
    description: '',
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