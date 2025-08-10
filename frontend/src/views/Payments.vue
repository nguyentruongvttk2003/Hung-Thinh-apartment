<template>
    <div class="payments-page">
    <div class="page-header">
      <h2>Quản lý thanh toán</h2>
    </div>

      <!-- Payments Table -->
      <el-card>
        <el-table
          :data="payments"
          v-loading="loading"
          style="width: 100%"
        >
          <el-table-column prop="invoice_id" label="Hóa đơn" width="100" />
          <el-table-column prop="amount" label="Số tiền" width="120">
            <template #default="{ row }">
              {{ formatCurrency(row.amount) }}
            </template>
          </el-table-column>
          <el-table-column prop="payment_method" label="Phương thức" width="120">
            <template #default="{ row }">
              <el-tag :type="getMethodTagType(row.payment_method)">
                {{ getMethodLabel(row.payment_method) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="payment_date" label="Ngày thanh toán" width="150">
            <template #default="{ row }">
              {{ formatDate(row.payment_date) }}
            </template>
          </el-table-column>
          <el-table-column prop="reference_number" label="Mã tham chiếu" />
          <el-table-column label="Thao tác" width="150" fixed="right">
            <template #default="{ row }">
              <el-button size="small" @click="viewPayment(row)">Xem</el-button>
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
import type { Payment } from '@/types'

// Data
const payments = ref<Payment[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// Methods
async function loadPayments() {
  loading.value = true
  try {
    console.log('Loading payments with params:', {
      page: currentPage.value,
      per_page: pageSize.value
    })
    
    const params = {
      page: currentPage.value,
      per_page: pageSize.value
    }
    const response = await api.getPayments(params)
    console.log('Payments API response:', response)
    console.log('Response type:', typeof response)
    console.log('Response keys:', Object.keys(response))
    
    payments.value = response.data || []
    total.value = response.total || 0
    console.log('Set payments:', payments.value.length, 'total:', total.value)
  } catch (error: any) {
    console.error('Payments load error:', error)
    console.error('Error response:', error.response?.data)
    ElMessage.error('Không thể tải danh sách thanh toán: ' + (error.message || 'Unknown error'))
  } finally {
    loading.value = false
  }
}

function getMethodLabel(method: string): string {
  const methodMap: Record<string, string> = {
    cash: 'Tiền mặt',
    bank_transfer: 'Chuyển khoản',
    online: 'Online'
  }
  return methodMap[method] || method
}

function getMethodTagType(method: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const methodTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    cash: 'info',
    bank_transfer: 'success',
    online: 'primary'
  }
  return methodTagMap[method] || 'primary'
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

function viewPayment(payment: Payment) {
  ElMessage.info(`Xem thanh toán: ${payment.id}`)
}

function handleSizeChange(size: number) {
  pageSize.value = size
  currentPage.value = 1
  loadPayments()
}

function handleCurrentChange(page: number) {
  currentPage.value = page
  loadPayments()
}

onMounted(() => {
  loadPayments()
})
</script>

<style scoped>
.payments-page {
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