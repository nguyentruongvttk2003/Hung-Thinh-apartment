<template>
    <div class="invoices-page">
    <div class="page-header">
      <h2>Quản lý hóa đơn</h2>
      <el-button type="primary" @click="showCreateDialog = true">
        <el-icon><Plus /></el-icon>
        Tạo hóa đơn
      </el-button>
    </div>

      <!-- Invoices Table -->
      <el-card>
        <el-table
          :data="invoices"
          v-loading="loading"
          style="width: 100%"
        >
          <el-table-column prop="apartment_id" label="Căn hộ" width="100" />
          <el-table-column prop="month" label="Tháng" width="80" />
          <el-table-column prop="year" label="Năm" width="80" />
          <el-table-column prop="total_amount" label="Tổng tiền" width="120">
            <template #default="{ row }">
              {{ formatCurrency(row.total_amount) }}
            </template>
          </el-table-column>
          <el-table-column prop="due_date" label="Hạn thanh toán" width="120">
            <template #default="{ row }">
              {{ formatDate(row.due_date) }}
            </template>
          </el-table-column>
          <el-table-column prop="status" label="Trạng thái" width="120">
            <template #default="{ row }">
              <el-tag :type="getStatusTagType(row.status)">
                {{ getStatusLabel(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Thao tác" width="200" fixed="right">
            <template #default="{ row }">
              <el-button size="small" @click="viewInvoice(row)">Xem</el-button>
              <el-button size="small" type="primary" @click="editInvoice(row)">Sửa</el-button>
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
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'

import api from '@/services/api'
import type { Invoice } from '@/types'
import { Plus } from '@element-plus/icons-vue'

// Data
const invoices = ref<Invoice[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const showCreateDialog = ref(false)

// Methods
async function loadInvoices() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getInvoices(params)
    invoices.value = response.data
    total.value = response.total
  } catch (error) {
    ElMessage.error('Không thể tải danh sách hóa đơn')
  } finally {
    loading.value = false
  }
}

function getStatusLabel(status: string): string {
  const statusMap: Record<string, string> = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    overdue: 'Quá hạn'
  }
  return statusMap[status] || status
}

function getStatusTagType(status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const statusTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    pending: 'warning',
    paid: 'success',
    overdue: 'danger'
  }
  return statusTagMap[status] || 'primary'
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount)
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('vi-VN')
}

function viewInvoice(invoice: Invoice) {
  ElMessage.info(`Xem hóa đơn: ${invoice.id}`)
}

function editInvoice(invoice: Invoice) {
  ElMessage.info(`Sửa hóa đơn: ${invoice.id}`)
}

function handleSizeChange(size: number) {
  pageSize.value = size
  currentPage.value = 1
  loadInvoices()
}

function handleCurrentChange(page: number) {
  currentPage.value = page
  loadInvoices()
}

onMounted(() => {
  loadInvoices()
})
</script>

<style scoped>
.invoices-page {
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