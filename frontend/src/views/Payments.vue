<template>
  <div class="payments-page">
    <div class="page-header">
      <h2>Quản lý thanh toán</h2>
      <div class="header-actions">
        <el-button type="primary" @click="showCreateDialog = true">
          <el-icon><Plus /></el-icon>
          Tạo thanh toán
        </el-button>
        <el-button @click="showStatsDialog = true">
          <el-icon><DataAnalysis /></el-icon>
          Thống kê
        </el-button>
      </div>
    </div>

    <!-- Filters -->
    <el-card class="filter-card">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-select
            v-model="filters.status"
            placeholder="Trạng thái thanh toán"
            clearable
            @change="loadPayments"
          >
            <el-option label="Tất cả" value="" />
            <el-option label="Chờ xử lý" value="pending" />
            <el-option label="Hoàn thành" value="completed" />
            <el-option label="Thất bại" value="failed" />
            <el-option label="Đã hủy" value="cancelled" />
            <el-option label="Hoàn tiền" value="refunded" />
          </el-select>
        </el-col>
        <el-col :span="6">
          <el-select
            v-model="filters.payment_method"
            placeholder="Phương thức thanh toán"
            clearable
            @change="loadPayments"
          >
            <el-option label="Tất cả" value="" />
            <el-option label="Tiền mặt" value="cash" />
            <el-option label="Chuyển khoản" value="bank_transfer" />
            <el-option label="QR Code" value="qr_code" />
            <el-option label="Thẻ tín dụng" value="credit_card" />
            <el-option label="Ví điện tử" value="e_wallet" />
          </el-select>
        </el-col>
        <el-col :span="6">
          <el-date-picker
            v-model="filters.date_range"
            type="daterange"
            range-separator="đến"
            start-placeholder="Từ ngày"
            end-placeholder="Đến ngày"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            @change="loadPayments"
          />
        </el-col>
        <el-col :span="6">
          <el-input
            v-model="filters.search"
            placeholder="Tìm kiếm..."
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

    <!-- Payments Table -->
    <el-card>
      <el-table
        :data="payments"
        v-loading="loading"
        style="width: 100%"
        @sort-change="handleSortChange"
      >
        <el-table-column prop="payment_number" label="Số phiếu" width="150" sortable />
        <el-table-column label="Hóa đơn" width="120">
          <template #default="{ row }">
            <el-link @click="viewInvoice(row.invoice)" type="primary">
              {{ row.invoice?.invoice_number || row.invoice_id }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="Căn hộ" width="120">
          <template #default="{ row }">
            {{ row.invoice?.apartment?.apartment_number || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="Người thanh toán" width="150">
          <template #default="{ row }">
            {{ row.user?.name || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="Số tiền" width="120" sortable>
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
        <el-table-column prop="status" label="Trạng thái" width="120">
          <template #default="{ row }">
            <el-tag :type="getStatusTagType(row.status)">
              {{ getStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="paid_at" label="Ngày thanh toán" width="150" sortable>
          <template #default="{ row }">
            {{ row.paid_at ? formatDate(row.paid_at) : '-' }}
          </template>
        </el-table-column>
        <el-table-column label="Thao tác" width="300" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button size="small" @click="viewPayment(row)">Xem</el-button>
              <el-button 
                v-if="row.status === 'pending'" 
                size="small" 
                type="success" 
                @click="processPayment(row)"
              >
                Xử lý
              </el-button>
              <el-button 
                v-if="row.status === 'pending'" 
                size="small" 
                type="primary" 
                @click="editPayment(row)"
              >
                Sửa
              </el-button>
              <el-button 
                v-if="['pending', 'failed'].includes(row.status)" 
                size="small" 
                type="danger" 
                @click="deletePayment(row)"
              >
                Xóa
              </el-button>
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

    <!-- Create/Edit Payment Dialog -->
    <el-dialog
      v-model="showCreateDialog"
      :title="editingPayment ? 'Sửa thanh toán' : 'Tạo thanh toán mới'"
      width="700px"
      @close="resetPaymentForm"
    >
      <el-form
        ref="paymentFormRef"
        :model="paymentForm"
        :rules="paymentRules"
        label-width="150px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Hóa đơn" prop="invoice_id">
              <el-select
                v-model="paymentForm.invoice_id"
                placeholder="Chọn hóa đơn"
                filterable
                remote
                :remote-method="searchInvoices"
                style="width: 100%"
              >
                <el-option
                  v-for="invoice in invoiceOptions"
                  :key="invoice.id"
                  :label="`${invoice.invoice_number} - ${invoice.apartment?.apartment_number}`"
                  :value="invoice.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Số tiền" prop="amount">
              <el-input-number
                v-model="paymentForm.amount"
                :min="0"
                :precision="0"
                style="width: 100%"
                placeholder="Nhập số tiền"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Phương thức" prop="payment_method">
              <el-select v-model="paymentForm.payment_method" placeholder="Chọn phương thức" style="width: 100%">
                <el-option label="Tiền mặt" value="cash" />
                <el-option label="Chuyển khoản" value="bank_transfer" />
                <el-option label="QR Code" value="qr_code" />
                <el-option label="Thẻ tín dụng" value="credit_card" />
                <el-option label="Ví điện tử" value="e_wallet" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Mã giao dịch" prop="transaction_id">
              <el-input
                v-model="paymentForm.transaction_id"
                placeholder="Nhập mã giao dịch (tùy chọn)"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Chi tiết thanh toán" prop="payment_details">
          <el-input
            v-model="paymentForm.payment_details"
            type="textarea"
            :rows="3"
            placeholder="Nhập chi tiết thanh toán (JSON array format, ví dụ: [{'bank': 'VCB', 'account': '123456'}])"
          />
        </el-form-item>

        <el-form-item label="Ghi chú" prop="notes">
          <el-input
            v-model="paymentForm.notes"
            type="textarea"
            :rows="3"
            placeholder="Nhập ghi chú"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="showCreateDialog = false">Hủy</el-button>
        <el-button type="primary" @click="savePayment" :loading="saving">
          {{ editingPayment ? 'Cập nhật' : 'Tạo' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- View Payment Dialog -->
    <el-dialog
      v-model="showViewDialog"
      title="Chi tiết thanh toán"
      width="700px"
    >
      <div v-if="viewingPayment" class="payment-detail">
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Số phiếu thanh toán:</label>
              <p>{{ viewingPayment.payment_number }}</p>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Hóa đơn:</label>
              <p>{{ viewingPayment.invoice?.invoice_number || viewingPayment.invoice_id }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Căn hộ:</label>
              <p>{{ viewingPayment.invoice?.apartment?.apartment_number || '-' }}</p>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Người thanh toán:</label>
              <p>{{ viewingPayment.user?.name || '-' }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Số tiền:</label>
              <p class="amount">{{ formatCurrency(viewingPayment.amount) }}</p>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Phương thức:</label>
              <el-tag :type="getMethodTagType(viewingPayment.payment_method)">
                {{ getMethodLabel(viewingPayment.payment_method) }}
              </el-tag>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-item">
              <label>Trạng thái:</label>
              <el-tag :type="getStatusTagType(viewingPayment.status)">
                {{ getStatusLabel(viewingPayment.status) }}
              </el-tag>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Ngày thanh toán:</label>
              <p>{{ viewingPayment.paid_at ? formatDate(viewingPayment.paid_at) : 'Chưa thanh toán' }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20" v-if="viewingPayment.transaction_id">
          <el-col :span="12">
            <div class="detail-item">
              <label>Mã giao dịch:</label>
              <p>{{ viewingPayment.transaction_id }}</p>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-item">
              <label>Người xử lý:</label>
              <p>{{ viewingPayment.processor?.name || 'Hệ thống' }}</p>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20" v-if="viewingPayment.payment_details">
          <el-col :span="24">
            <div class="detail-item">
              <label>Chi tiết thanh toán:</label>
              <pre>{{ formatPaymentDetails(viewingPayment.payment_details) }}</pre>
            </div>
          </el-col>
        </el-row>

        <el-row :gutter="20" v-if="viewingPayment.notes">
          <el-col :span="24">
            <div class="detail-item">
              <label>Ghi chú:</label>
              <p>{{ viewingPayment.notes }}</p>
            </div>
          </el-col>
        </el-row>
      </div>

      <template #footer>
        <el-button @click="showViewDialog = false">Đóng</el-button>
        <el-button 
          v-if="viewingPayment?.status === 'pending'" 
          type="success" 
          @click="processPaymentFromView"
        >
          Xử lý thanh toán
        </el-button>
        <el-button 
          v-if="viewingPayment?.status === 'pending'" 
          type="primary" 
          @click="editPaymentFromView"
        >
          Sửa
        </el-button>
      </template>
    </el-dialog>

    <!-- Stats Dialog -->
    <el-dialog
      v-model="showStatsDialog"
      title="Thống kê thanh toán"
      width="800px"
    >
      <div v-loading="statsLoading">
        <el-row :gutter="20">
          <el-col :span="6">
            <el-statistic
              title="Tổng thanh toán"
              :value="stats.total_payments"
              suffix="khoản"
            />
          </el-col>
          <el-col :span="6">
            <el-statistic
              title="Tổng tiền thu"
              :value="stats.total_amount"
              :formatter="formatCurrency"
            />
          </el-col>
          <el-col :span="6">
            <el-statistic
              title="Chờ xử lý"
              :value="stats.pending_payments"
              suffix="khoản"
            />
          </el-col>
          <el-col :span="6">
            <el-statistic
              title="Hoàn thành"
              :value="stats.completed_payments"
              suffix="khoản"
            />
          </el-col>
        </el-row>
      </div>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox, type FormInstance, type FormRules } from 'element-plus'
import { Plus, DataAnalysis, Search } from '@element-plus/icons-vue'

import api from '@/services/api'
import type { Payment, Invoice } from '@/types'

// Form interface
interface PaymentForm {
  invoice_id: number | null
  amount: number
  payment_method: string
  transaction_id: string
  payment_details: string
  notes: string
}

// Data
const payments = ref<Payment[]>([])
const invoiceOptions = ref<Invoice[]>([])
const loading = ref(false)
const saving = ref(false)
const statsLoading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// Dialog states
const showCreateDialog = ref(false)
const showViewDialog = ref(false)
const showStatsDialog = ref(false)
const editingPayment = ref<Payment | null>(null)
const viewingPayment = ref<Payment | null>(null)
const paymentFormRef = ref<FormInstance>()

// Filters
const filters = reactive({
  status: '',
  payment_method: '',
  date_range: [],
  search: ''
})

// Stats
const stats = reactive({
  total_payments: 0,
  total_amount: 0,
  pending_payments: 0,
  completed_payments: 0
})

// Form
const paymentForm = reactive<PaymentForm>({
  invoice_id: null,
  amount: 0,
  payment_method: '',
  transaction_id: '',
  payment_details: '',
  notes: ''
})

// Form validation rules
const paymentRules: FormRules = {
  invoice_id: [
    { required: true, message: 'Vui lòng chọn hóa đơn', trigger: 'change' }
  ],
  amount: [
    { required: true, message: 'Vui lòng nhập số tiền', trigger: 'blur' },
    { type: 'number', min: 1, message: 'Số tiền phải lớn hơn 0', trigger: 'blur' }
  ],
  payment_method: [
    { required: true, message: 'Vui lòng chọn phương thức thanh toán', trigger: 'change' }
  ]
}

// Methods
async function loadPayments() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value,
      ...filters,
      from_date: filters.date_range[0] || '',
      to_date: filters.date_range[1] || ''
    }
    
    const response = await api.getPayments(params)
    payments.value = response.data || []
    total.value = response.total || 0
  } catch (error: any) {
    console.error('Payments load error:', error)
    ElMessage.error('Không thể tải danh sách thanh toán: ' + (error.message || 'Unknown error'))
  } finally {
    loading.value = false
  }
}

async function loadStats() {
  statsLoading.value = true
  try {
    const response = await api.getPaymentStats()
    Object.assign(stats, response.data)
  } catch (error: any) {
    console.error('Stats load error:', error)
    ElMessage.error('Không thể tải thống kê')
  } finally {
    statsLoading.value = false
  }
}

async function searchInvoices(query: string) {
  if (!query) return
  try {
    // This would need to be implemented in the API
    const response = await api.getInvoices({ search: query, status: 'pending' })
    invoiceOptions.value = response.data || []
  } catch (error) {
    console.error('Search invoices error:', error)
  }
}

function getMethodLabel(method: string): string {
  const methodMap: Record<string, string> = {
    cash: 'Tiền mặt',
    bank_transfer: 'Chuyển khoản',
    qr_code: 'QR Code',
    credit_card: 'Thẻ tín dụng',
    e_wallet: 'Ví điện tử'
  }
  return methodMap[method] || method
}

function getMethodTagType(method: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const methodTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    cash: 'info',
    bank_transfer: 'success',
    qr_code: 'primary',
    credit_card: 'warning',
    e_wallet: 'primary'
  }
  return methodTagMap[method] || 'primary'
}

function getStatusLabel(status: string): string {
  const statusMap: Record<string, string> = {
    pending: 'Chờ xử lý',
    completed: 'Hoàn thành',
    failed: 'Thất bại',
    cancelled: 'Đã hủy',
    refunded: 'Hoàn tiền'
  }
  return statusMap[status] || status
}

function getStatusTagType(status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const statusTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    pending: 'warning',
    completed: 'success',
    failed: 'danger',
    cancelled: 'info',
    refunded: 'primary'
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

function formatPaymentDetails(details: any): string {
  if (typeof details === 'string') {
    try {
      return JSON.stringify(JSON.parse(details), null, 2)
    } catch {
      return details
    }
  }
  return JSON.stringify(details, null, 2)
}

function viewPayment(payment: Payment) {
  viewingPayment.value = payment
  showViewDialog.value = true
}

function viewInvoice(invoice: Invoice) {
  // This could open invoice detail or navigate to invoice page
  ElMessage.info(`Xem hóa đơn: ${invoice.invoice_number}`)
}

function editPayment(payment: Payment) {
  editingPayment.value = payment
  Object.assign(paymentForm, {
    invoice_id: payment.invoice_id,
    amount: payment.amount,
    payment_method: payment.payment_method,
    transaction_id: payment.transaction_id || '',
    payment_details: payment.payment_details 
      ? JSON.stringify(payment.payment_details) 
      : '',
    notes: payment.notes || ''
  })
  showCreateDialog.value = true
}

function editPaymentFromView() {
  if (viewingPayment.value) {
    showViewDialog.value = false
    editPayment(viewingPayment.value)
  }
}

async function processPayment(payment: Payment) {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xử lý thanh toán ${payment.payment_number}?`,
      'Xác nhận xử lý',
      {
        confirmButtonText: 'Xử lý',
        cancelButtonText: 'Hủy',
        type: 'warning',
      }
    )
    
    await api.processPayment(payment.id)
    ElMessage.success('Đã xử lý thanh toán thành công')
    loadPayments()
  } catch (error: any) {
    if (error !== 'cancel') {
      console.error('Process payment error:', error)
      ElMessage.error('Có lỗi xảy ra khi xử lý thanh toán')
    }
  }
}

async function processPaymentFromView() {
  if (viewingPayment.value) {
    showViewDialog.value = false
    await processPayment(viewingPayment.value)
  }
}

async function deletePayment(payment: Payment) {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa thanh toán ${payment.payment_number}?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning',
      }
    )
    
    await api.deletePayment(payment.id)
    ElMessage.success('Đã xóa thanh toán')
    loadPayments()
  } catch (error: any) {
    if (error !== 'cancel') {
      console.error('Delete payment error:', error)
      ElMessage.error('Có lỗi xảy ra khi xóa thanh toán')
    }
  }
}

function resetPaymentForm() {
  Object.assign(paymentForm, {
    invoice_id: null,
    amount: 0,
    payment_method: '',
    transaction_id: '',
    payment_details: '',
    notes: ''
  })
  editingPayment.value = null
  paymentFormRef.value?.clearValidate()
}

async function savePayment() {
  if (!paymentFormRef.value) return
  
  try {
    const valid = await paymentFormRef.value.validate()
    if (!valid) return
    
    saving.value = true
    
    // Process payment_details to ensure it's an array or null
    let processedPaymentDetails = null
    if (paymentForm.payment_details && paymentForm.payment_details.trim()) {
      try {
        const parsed = JSON.parse(paymentForm.payment_details)
        // Ensure the parsed result is an array
        if (Array.isArray(parsed)) {
          processedPaymentDetails = parsed
        } else if (typeof parsed === 'object' && parsed !== null) {
          // Convert object to array format
          processedPaymentDetails = [parsed]
        } else {
          // If it's a primitive value, wrap it in an array
          processedPaymentDetails = [parsed]
        }
      } catch (error) {
        // If JSON parsing fails, treat as string and wrap in array
        processedPaymentDetails = [{ note: paymentForm.payment_details }]
      }
    }

    const paymentData = {
      invoice_id: paymentForm.invoice_id!,  // Assert non-null since validation passed
      amount: paymentForm.amount,
      payment_method: paymentForm.payment_method as 'cash' | 'bank_transfer' | 'qr_code' | 'credit_card' | 'e_wallet',
      transaction_id: paymentForm.transaction_id,
      notes: paymentForm.notes,
      payment_details: processedPaymentDetails
    }
    
    if (editingPayment.value) {
      await api.updatePayment(editingPayment.value.id, paymentData)
      ElMessage.success('Cập nhật thanh toán thành công')
    } else {
      await api.createPayment(paymentData)
      ElMessage.success('Tạo thanh toán thành công')
    }
    
    showCreateDialog.value = false
    resetPaymentForm()
    loadPayments()
  } catch (error: any) {
    console.error('Save payment error:', error)
    ElMessage.error('Có lỗi xảy ra khi lưu thanh toán')
  } finally {
    saving.value = false
  }
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

function handleSortChange(sort: any) {
  // Implement sorting logic
  console.log('Sort change:', sort)
}

let searchTimeout: any
function handleSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    loadPayments()
  }, 500)
}

onMounted(() => {
  loadPayments()
})

// Watch for stats dialog opening
watch(() => showStatsDialog.value, (newVal: boolean) => {
  if (newVal) {
    loadStats()
  }
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

.header-actions {
  display: flex;
  gap: 10px;
}

.filter-card {
  margin-bottom: 20px;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.payment-detail .detail-item {
  margin-bottom: 16px;
}

.payment-detail .detail-item label {
  font-weight: 600;
  color: #606266;
  display: block;
  margin-bottom: 4px;
}

.payment-detail .detail-item p {
  margin: 0;
  color: #303133;
}

.payment-detail .detail-item .amount {
  font-size: 18px;
  font-weight: 600;
  color: #67C23A;
}

.payment-detail .detail-item pre {
  background: #f5f7fa;
  padding: 10px;
  border-radius: 4px;
  margin: 0;
  white-space: pre-wrap;
  font-size: 12px;
  max-height: 200px;
  overflow-y: auto;
}

.el-statistic {
  text-align: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: nowrap;
  align-items: center;
}

.action-buttons .el-button {
  margin: 0;
  min-width: 60px;
}

.action-buttons .el-button--small {
  padding: 5px 12px;
  font-size: 12px;
}
</style> 