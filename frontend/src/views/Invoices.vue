<template>
    <div class="invoices-page">
    <div class="page-header">
      <h2>Quản lý hóa đơn</h2>
      <el-button type="primary" @click="resetInvoiceForm(); editingInvoice = null; showCreateDialog = true">
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
          <el-table-column prop="invoice_number" label="Số hóa đơn" width="150" />
          <el-table-column label="Căn hộ" width="120">
            <template #default="{ row }">
              {{ row.apartment?.apartment_number || row.apartment_id }}
            </template>
          </el-table-column>
          <el-table-column label="Kỳ tính phí" width="120">
            <template #default="{ row }">
              {{ getBillingPeriod(row) }}
            </template>
          </el-table-column>
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
          <el-table-column label="Thao tác" width="250" fixed="right">
            <template #default="{ row }">
              <el-button size="small" @click="viewInvoice(row)">Xem</el-button>
              <el-button size="small" type="primary" @click="editInvoice(row)">Sửa</el-button>
              <el-button size="small" type="danger" @click="deleteInvoice(row)">Xóa</el-button>
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
        :title="editingInvoice ? 'Sửa hóa đơn' : 'Tạo hóa đơn mới'"
        width="600px"
      >
        <el-form
          ref="invoiceFormRef"
          :model="invoiceForm"
          :rules="invoiceRules"
          label-width="120px"
        >
          <el-form-item label="Căn hộ" prop="apartment_id">
            <el-select v-model="invoiceForm.apartment_id" placeholder="Chọn căn hộ" style="width: 100%">
              <el-option
                v-for="apt in apartments"
                :key="apt.id"
                :label="`${apt.apartment_number} - Block ${apt.block}`"
                :value="apt.id"
              />
            </el-select>
          </el-form-item>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="Tháng" prop="month">
                <el-select v-model="invoiceForm.month" placeholder="Chọn tháng">
                  <el-option v-for="i in 12" :key="i" :label="`Tháng ${i}`" :value="i.toString()" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="Năm" prop="year">
                <el-input-number v-model="invoiceForm.year" :min="2020" :max="2030" style="width: 100%" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-form-item label="Phí quản lý" prop="management_fee">
            <el-input-number v-model="invoiceForm.management_fee" :min="0" style="width: 100%" />
          </el-form-item>
          
          <el-form-item label="Tiền điện" prop="electricity_fee">
            <el-input-number v-model="invoiceForm.electricity_fee" :min="0" style="width: 100%" />
          </el-form-item>
          
          <el-form-item label="Tiền nước" prop="water_fee">
            <el-input-number v-model="invoiceForm.water_fee" :min="0" style="width: 100%" />
          </el-form-item>
          
          <el-form-item label="Phí đỗ xe" prop="parking_fee">
            <el-input-number v-model="invoiceForm.parking_fee" :min="0" style="width: 100%" />
          </el-form-item>
          
          <el-form-item label="Phí khác" prop="other_fees">
            <el-input-number v-model="invoiceForm.other_fees" :min="0" style="width: 100%" />
          </el-form-item>
          
          <el-form-item label="Hạn thanh toán" prop="due_date">
            <el-date-picker
              v-model="invoiceForm.due_date"
              type="date"
              placeholder="Chọn ngày"
              style="width: 100%"
              format="DD/MM/YYYY"
              value-format="YYYY-MM-DD"
            />
          </el-form-item>
          
          <el-form-item label="Ghi chú" prop="notes">
            <el-input
              v-model="invoiceForm.notes"
              type="textarea"
              :rows="3"
              placeholder="Nhập ghi chú (không bắt buộc)"
            />
          </el-form-item>
        </el-form>
        
        <template #footer>
          <el-button @click="showCreateDialog = false">Hủy</el-button>
          <el-button type="primary" @click="saveInvoice" :loading="saving">
            {{ editingInvoice ? 'Cập nhật' : 'Tạo' }}
          </el-button>
        </template>
      </el-dialog>

      <!-- View Dialog -->
      <el-dialog
        v-model="showViewDialog"
        title="Chi tiết hóa đơn"
        width="600px"
      >
        <div v-if="viewingInvoice" class="invoice-detail">
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Số hóa đơn:</label>
                <p>{{ viewingInvoice.invoice_number }}</p>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Căn hộ:</label>
                <p>{{ viewingInvoice.apartment?.apartment_number || viewingInvoice.apartment_id }}</p>
              </div>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Kỳ tính phí:</label>
                <p>{{ getBillingPeriod(viewingInvoice) }}</p>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Hạn thanh toán:</label>
                <p>{{ formatDate(viewingInvoice.due_date) }}</p>
              </div>
            </el-col>
          </el-row>

          <el-divider content-position="left">Chi tiết phí</el-divider>

          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Phí quản lý:</label>
                <p>{{ formatCurrency(viewingInvoice.management_fee) }}</p>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Tiền điện:</label>
                <p>{{ formatCurrency(viewingInvoice.electricity_fee) }}</p>
              </div>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Tiền nước:</label>
                <p>{{ formatCurrency(viewingInvoice.water_fee) }}</p>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Phí đỗ xe:</label>
                <p>{{ formatCurrency(viewingInvoice.parking_fee) }}</p>
              </div>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Phí khác:</label>
                <p>{{ formatCurrency(viewingInvoice.other_fees || 0) }}</p>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Tổng tiền:</label>
                <p class="total-amount">{{ formatCurrency(viewingInvoice.total_amount) }}</p>
              </div>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <div class="detail-item">
                <label>Trạng thái:</label>
                <el-tag :type="getStatusTagType(viewingInvoice.status)">
                  {{ getStatusLabel(viewingInvoice.status) }}
                </el-tag>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="detail-item">
                <label>Ghi chú:</label>
                <p>{{ viewingInvoice.notes || 'Không có' }}</p>
              </div>
            </el-col>
          </el-row>
        </div>

        <template #footer>
          <el-button @click="showViewDialog = false">Đóng</el-button>
          <el-button type="primary" @click="editInvoiceFromView">Sửa</el-button>
        </template>
      </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox, type FormInstance, type FormRules } from 'element-plus'

import api from '@/services/api'
import type { Invoice, Apartment } from '@/types'
import { Plus } from '@element-plus/icons-vue'

// Form interface
interface InvoiceForm {
  apartment_id: number | null
  month: string
  year: number
  management_fee: number
  electricity_fee: number
  water_fee: number
  parking_fee: number
  other_fees: number
  due_date: string
  notes?: string
}

// Data
const invoices = ref<Invoice[]>([])
const apartments = ref<Apartment[]>([])
const loading = ref(false)
const saving = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// Dialog states
const showCreateDialog = ref(false)
const showViewDialog = ref(false)
const editingInvoice = ref<Invoice | null>(null)
const viewingInvoice = ref<Invoice | null>(null)
const invoiceFormRef = ref<FormInstance>()

// Form
const invoiceForm = reactive<InvoiceForm>({
  apartment_id: null,
  month: '',
  year: new Date().getFullYear(),
  management_fee: 0,
  electricity_fee: 0,
  water_fee: 0,
  parking_fee: 0,
  other_fees: 0,
  due_date: '',
  notes: ''
})

const invoiceRules: FormRules = {
  apartment_id: [
    { required: true, message: 'Vui lòng chọn căn hộ', trigger: 'change' }
  ],
  month: [
    { required: true, message: 'Vui lòng chọn tháng', trigger: 'change' }
  ],
  year: [
    { required: true, message: 'Vui lòng nhập năm', trigger: 'blur' }
  ],
  due_date: [
    { required: true, message: 'Vui lòng chọn hạn thanh toán', trigger: 'change' }
  ]
}

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
    partial: 'Thanh toán một phần',
    paid: 'Đã thanh toán',
    overdue: 'Quá hạn',
    cancelled: 'Đã hủy'
  }
  return statusMap[status] || status
}

function getStatusTagType(status: string): 'primary' | 'success' | 'warning' | 'info' | 'danger' {
  const statusTagMap: Record<string, 'primary' | 'success' | 'warning' | 'info' | 'danger'> = {
    pending: 'warning',
    partial: 'info',
    paid: 'success',
    overdue: 'danger',
    cancelled: 'info'
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

function getBillingPeriod(invoice: Invoice): string {
  if (invoice.billing_period_start && invoice.billing_period_end) {
    const start = new Date(invoice.billing_period_start)
    const end = new Date(invoice.billing_period_end)
    return `${start.toLocaleDateString('vi-VN')} - ${end.toLocaleDateString('vi-VN')}`
  }
  // Return invoice number as fallback
  return invoice.invoice_number || 'N/A'
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

async function loadApartments() {
  try {
    const response = await api.getApartments()
    apartments.value = response.data || []
  } catch (error) {
    console.error('Error loading apartments:', error)
  }
}

function resetInvoiceForm() {
  Object.assign(invoiceForm, {
    apartment_id: null,
    month: '',
    year: new Date().getFullYear(),
    management_fee: 0,
    electricity_fee: 0,
    water_fee: 0,
    parking_fee: 0,
    other_fees: 0,
    due_date: '',
    notes: ''
  })
  invoiceFormRef.value?.resetFields()
}

function viewInvoice(invoice: Invoice) {
  console.log('Viewing invoice:', invoice)
  viewingInvoice.value = invoice
  showViewDialog.value = true
}

function editInvoice(invoice: Invoice) {
  console.log('Editing invoice:', invoice)
  editingInvoice.value = invoice
  
  // Extract month and year from billing period
  const billingStart = new Date(invoice.billing_period_start)
  const month = (billingStart.getMonth() + 1).toString()
  const year = billingStart.getFullYear()
  
  // Populate form with invoice data
  Object.assign(invoiceForm, {
    apartment_id: invoice.apartment_id,
    month: month,
    year: year,
    management_fee: invoice.management_fee,
    electricity_fee: invoice.electricity_fee,
    water_fee: invoice.water_fee,
    parking_fee: invoice.parking_fee,
    other_fees: invoice.other_fees,
    due_date: invoice.due_date,
    notes: invoice.notes || ''
  })
  
  showCreateDialog.value = true
}

function editInvoiceFromView() {
  if (viewingInvoice.value) {
    showViewDialog.value = false
    editInvoice(viewingInvoice.value)
  }
}

async function deleteInvoice(invoice: Invoice) {
  try {
    await ElMessageBox.confirm(
      `Bạn có chắc chắn muốn xóa hóa đơn ${invoice.invoice_number} của căn hộ ${invoice.apartment?.apartment_number || invoice.apartment_id}?`,
      'Xác nhận xóa',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning'
      }
    )
    
    await api.deleteInvoice(invoice.id)
    ElMessage.success('Xóa hóa đơn thành công')
    loadInvoices()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Delete invoice error:', error)
      ElMessage.error('Không thể xóa hóa đơn')
    }
  }
}

async function saveInvoice() {
  if (!invoiceFormRef.value) return
  
  try {
    await invoiceFormRef.value.validate()
    saving.value = true
    
    // Calculate total amount
    const totalAmount = invoiceForm.management_fee + invoiceForm.electricity_fee + 
                       invoiceForm.water_fee + invoiceForm.parking_fee + invoiceForm.other_fees
    
    const invoiceData = {
      ...invoiceForm,
      apartment_id: invoiceForm.apartment_id!,  // Assert non-null since validation passed
      total_amount: totalAmount
    }
    
    if (editingInvoice.value) {
      await api.updateInvoice(editingInvoice.value.id, invoiceData)
      ElMessage.success('Cập nhật hóa đơn thành công')
    } else {
      await api.createInvoice(invoiceData)
      ElMessage.success('Tạo hóa đơn thành công')
    }
    
    showCreateDialog.value = false
    editingInvoice.value = null
    resetInvoiceForm()
    loadInvoices()
  } catch (error) {
    console.error('Save invoice error:', error)
    ElMessage.error('Có lỗi xảy ra khi lưu hóa đơn')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadInvoices()
  loadApartments()
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

.invoice-detail .detail-item {
  margin-bottom: 16px;
}

.invoice-detail .detail-item label {
  font-weight: 600;
  color: #606266;
  display: block;
  margin-bottom: 4px;
}

.invoice-detail .detail-item p {
  margin: 0;
  color: #303133;
}

.invoice-detail .total-amount {
  font-weight: 700;
  font-size: 16px;
  color: #409eff;
}
</style> 