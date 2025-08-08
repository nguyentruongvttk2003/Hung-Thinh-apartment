<template>
  <div class="data-table">
    <!-- Table Toolbar -->
    <el-card v-if="showToolbar" class="toolbar-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <!-- Search -->
          <el-input
            v-if="searchable"
            v-model="searchQuery"
            :placeholder="searchPlaceholder"
            style="width: 300px"
            clearable
            @input="handleSearch"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          
          <!-- Filters -->
          <div v-if="filters && filters.length > 0" class="filters">
            <template v-for="filter in filters" :key="filter.key">
              <el-select
                v-model="filterValues[filter.key]"
                :placeholder="filter.placeholder"
                clearable
                @change="handleFilter"
              >
                <el-option
                  v-for="option in filter.options"
                  :key="option.value"
                  :label="option.label"
                  :value="option.value"
                />
              </el-select>
            </template>
          </div>
        </div>
        
        <div class="toolbar-right">
          <!-- Action Buttons -->
          <slot name="toolbar-actions" />
          
          <!-- Refresh Button -->
          <el-button
            v-if="refreshable"
            :loading="loading"
            @click="handleRefresh"
          >
            <el-icon><Refresh /></el-icon>
            Làm mới
          </el-button>
          
          <!-- Export Button -->
          <el-button
            v-if="exportable"
            @click="handleExport"
          >
            <el-icon><Download /></el-icon>
            Xuất Excel
          </el-button>
        </div>
      </div>
    </el-card>

    <!-- Main Table Card -->
    <el-card>
      <!-- Table Header -->
      <template v-if="title" #header>
        <div class="table-header">
          <h3>{{ title }}</h3>
          <div class="table-stats" v-if="showStats">
            <el-tag>
              Tổng: {{ total }} bản ghi
            </el-tag>
            <el-tag v-if="filtered" type="info">
              Đã lọc: {{ filteredData.length }} bản ghi
            </el-tag>
          </div>
        </div>
      </template>

      <!-- Table -->
      <el-table
        ref="tableRef"
        :data="tableData"
        v-loading="loading"
        :stripe="stripe"
        :border="border"
        :size="size"
        :height="height"
        :max-height="maxHeight"
        :highlight-current-row="highlightCurrentRow"
        :row-key="rowKey"
        :tree-props="treeProps"
        :lazy="lazy"
        :load="load"
        :default-sort="defaultSort"
        :show-summary="showSummary"
        :sum-text="sumText"
        :summary-method="summaryMethod"
        @selection-change="handleSelectionChange"
        @sort-change="handleSortChange"
        @row-click="handleRowClick"
        @row-dblclick="handleRowDoubleClick"
      >
        <!-- Selection Column -->
        <el-table-column
          v-if="selectable"
          type="selection"
          width="50"
          :selectable="selectableFunction"
        />
        
        <!-- Index Column -->
        <el-table-column
          v-if="showIndex"
          type="index"
          label="#"
          width="60"
          :index="indexMethod"
        />

        <!-- Dynamic Columns -->
        <template v-for="column in columns" :key="column.prop || column.type">
          <el-table-column
            :prop="column.prop"
            :label="column.label"
            :width="column.width"
            :min-width="column.minWidth"
            :fixed="column.fixed"
            :sortable="column.sortable"
            :sort-method="column.sortMethod"
            :formatter="column.formatter"
            :show-overflow-tooltip="column.showOverflowTooltip"
            :align="column.align"
            :header-align="column.headerAlign"
            :class-name="column.className"
            :label-class-name="column.labelClassName"
            :filters="column.filters"
            :filter-method="column.filterMethod"
            :filter-multiple="column.filterMultiple"
            :filter-placement="column.filterPlacement"
          >
            <!-- Custom Column Content -->
            <template v-if="column.slot" #default="scope">
              <slot
                :name="column.slot"
                :row="scope.row"
                :column="scope.column"
                :$index="scope.$index"
              />
            </template>
            
            <!-- Column Header -->
            <template v-if="column.headerSlot" #header="scope">
              <slot
                :name="column.headerSlot"
                :column="scope.column"
                :$index="scope.$index"
              />
            </template>
          </el-table-column>
        </template>

        <!-- Actions Column -->
        <el-table-column
          v-if="actions && actions.length > 0"
          label="Thao tác"
          :width="actionsWidth"
          :fixed="actionsFixed"
          align="center"
        >
          <template #default="scope">
            <div class="table-actions">
              <template v-for="action in actions" :key="action.key">
                <el-button
                  v-if="!action.show || action.show(scope.row)"
                  :size="action.size || 'small'"
                  :type="action.type"
                  :plain="action.plain"
                  :loading="action.loading && action.loading(scope.row)"
                  :disabled="action.disabled && action.disabled(scope.row)"
                  @click="action.handler(scope.row, scope.$index)"
                >
                  <el-icon v-if="action.icon">
                    <component :is="action.icon" />
                  </el-icon>
                  {{ action.label }}
                </el-button>
                
                <el-dropdown
                  v-if="action.type === 'dropdown'"
                  @command="(command) => action.handler(command, scope.row, scope.$index)"
                >
                  <el-button :size="action.size || 'small'" :type="action.buttonType">
                    {{ action.label }}
                    <el-icon><ArrowDown /></el-icon>
                  </el-button>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item
                        v-for="item in action.items"
                        :key="item.key"
                        :command="item.key"
                        :disabled="item.disabled && item.disabled(scope.row)"
                      >
                        <el-icon v-if="item.icon">
                          <component :is="item.icon" />
                        </el-icon>
                        {{ item.label }}
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </template>
            </div>
          </template>
        </el-table-column>

        <!-- Empty slot -->
        <template #empty>
          <div class="empty-state">
            <el-empty
              :image-size="120"
              :description="emptyText"
            >
              <template v-if="$slots.empty">
                <slot name="empty" />
              </template>
            </el-empty>
          </div>
        </template>
      </el-table>

      <!-- Pagination -->
      <div v-if="pagination" class="pagination-wrapper">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="pageSizes"
          :total="total"
          :layout="paginationLayout"
          :hide-on-single-page="hideOnSinglePage"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Refresh, Download, ArrowDown } from '@element-plus/icons-vue'
import * as XLSX from 'xlsx'

// Props
interface TableColumn {
  prop?: string
  label: string
  width?: string | number
  minWidth?: string | number
  fixed?: boolean | string
  sortable?: boolean | string
  sortMethod?: Function
  formatter?: Function
  showOverflowTooltip?: boolean
  align?: string
  headerAlign?: string
  className?: string
  labelClassName?: string
  filters?: Array<{ text: string; value: any }>
  filterMethod?: Function
  filterMultiple?: boolean
  filterPlacement?: string
  slot?: string
  headerSlot?: string
}

interface TableAction {
  key: string
  label: string
  type?: string
  buttonType?: string
  size?: string
  plain?: boolean
  icon?: any
  handler: Function
  show?: Function
  disabled?: Function
  loading?: Function
  items?: Array<{
    key: string
    label: string
    icon?: any
    disabled?: Function
  }>
}

interface FilterOption {
  key: string
  placeholder: string
  options: Array<{ label: string; value: any }>
}

const props = withDefaults(defineProps<{
  data: any[]
  columns: TableColumn[]
  loading?: boolean
  total?: number
  currentPage?: number
  pageSize?: number
  
  // Table features
  searchable?: boolean
  searchPlaceholder?: string
  searchKeys?: string[]
  filters?: FilterOption[]
  refreshable?: boolean
  exportable?: boolean
  selectable?: boolean
  selectableFunction?: Function
  showIndex?: boolean
  indexMethod?: Function
  
  // Table appearance
  title?: string
  showStats?: boolean
  stripe?: boolean
  border?: boolean
  size?: string
  height?: string | number
  maxHeight?: string | number
  highlightCurrentRow?: boolean
  
  // Table behavior
  rowKey?: string
  treeProps?: object
  lazy?: boolean
  load?: Function
  defaultSort?: object
  showSummary?: boolean
  sumText?: string
  summaryMethod?: Function
  
  // Actions
  actions?: TableAction[]
  actionsWidth?: string | number
  actionsFixed?: boolean | string
  
  // Toolbar
  showToolbar?: boolean
  
  // Pagination
  pagination?: boolean
  pageSizes?: number[]
  paginationLayout?: string
  hideOnSinglePage?: boolean
  
  // Empty state
  emptyText?: string
}>(), {
  loading: false,
  total: 0,
  currentPage: 1,
  pageSize: 20,
  searchable: true,
  searchPlaceholder: 'Tìm kiếm...',
  refreshable: true,
  exportable: false,
  selectable: false,
  showIndex: false,
  showStats: true,
  stripe: true,
  border: false,
  size: 'default',
  highlightCurrentRow: true,
  showToolbar: true,
  pagination: true,
  pageSizes: () => [10, 20, 50, 100],
  paginationLayout: 'total, sizes, prev, pager, next, jumper',
  hideOnSinglePage: false,
  actionsWidth: 200,
  actionsFixed: 'right',
  emptyText: 'Không có dữ liệu'
})

// Emits
const emit = defineEmits<{
  'update:currentPage': [page: number]
  'update:pageSize': [size: number]
  'size-change': [size: number]
  'current-change': [page: number]
  'selection-change': [selection: any[]]
  'sort-change': [sortInfo: any]
  'row-click': [row: any, column: any, event: Event]
  'row-dblclick': [row: any, column: any, event: Event]
  'refresh': []
  'search': [query: string]
  'filter': [filters: Record<string, any>]
}>()

// Reactive state
const tableRef = ref()
const searchQuery = ref('')
const filterValues = ref<Record<string, any>>({})

// Computed
const filteredData = computed(() => {
  let result = [...props.data]
  
  // Search filter
  if (searchQuery.value && props.searchable) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(item => {
      if (props.searchKeys) {
        return props.searchKeys.some(key => 
          String(item[key] || '').toLowerCase().includes(query)
        )
      } else {
        return Object.values(item).some(value => 
          String(value || '').toLowerCase().includes(query)
        )
      }
    })
  }
  
  // Custom filters
  if (props.filters) {
    props.filters.forEach(filter => {
      const value = filterValues.value[filter.key]
      if (value !== undefined && value !== '') {
        result = result.filter(item => item[filter.key] === value)
      }
    })
  }
  
  return result
})

const tableData = computed(() => {
  if (props.pagination) {
    return filteredData.value
  }
  return filteredData.value
})

const filtered = computed(() => {
  return searchQuery.value || Object.values(filterValues.value).some(v => v !== undefined && v !== '')
})

// Methods
const handleSearch = () => {
  emit('search', searchQuery.value)
}

const handleFilter = () => {
  emit('filter', filterValues.value)
}

const handleRefresh = () => {
  emit('refresh')
}

const handleExport = () => {
  try {
    const exportData = filteredData.value.map(item => {
      const row: Record<string, any> = {}
      props.columns.forEach(column => {
        if (column.prop) {
          row[column.label] = item[column.prop]
        }
      })
      return row
    })
    
    const worksheet = XLSX.utils.json_to_sheet(exportData)
    const workbook = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Data')
    
    const fileName = `${props.title || 'data'}_${new Date().toISOString().split('T')[0]}.xlsx`
    XLSX.writeFile(workbook, fileName)
    
    ElMessage.success('Xuất file thành công')
  } catch (error) {
    ElMessage.error('Lỗi khi xuất file')
  }
}

const handleSizeChange = (size: number) => {
  emit('update:pageSize', size)
  emit('size-change', size)
}

const handleCurrentChange = (page: number) => {
  emit('update:currentPage', page)
  emit('current-change', page)
}

const handleSelectionChange = (selection: any[]) => {
  emit('selection-change', selection)
}

const handleSortChange = (sortInfo: any) => {
  emit('sort-change', sortInfo)
}

const handleRowClick = (row: any, column: any, event: Event) => {
  emit('row-click', row, column, event)
}

const handleRowDoubleClick = (row: any, column: any, event: Event) => {
  emit('row-dblclick', row, column, event)
}

// Public methods
const clearSelection = () => {
  tableRef.value?.clearSelection()
}

const toggleRowSelection = (row: any, selected?: boolean) => {
  tableRef.value?.toggleRowSelection(row, selected)
}

const toggleAllSelection = () => {
  tableRef.value?.toggleAllSelection()
}

const setCurrentRow = (row: any) => {
  tableRef.value?.setCurrentRow(row)
}

const clearSort = () => {
  tableRef.value?.clearSort()
}

const doLayout = () => {
  tableRef.value?.doLayout()
}

const sort = (prop: string, order: string) => {
  tableRef.value?.sort(prop, order)
}

// Expose methods
defineExpose({
  clearSelection,
  toggleRowSelection,
  toggleAllSelection,
  setCurrentRow,
  clearSort,
  doLayout,
  sort,
  tableRef
})

// Initialize filters
onMounted(() => {
  if (props.filters) {
    props.filters.forEach(filter => {
      filterValues.value[filter.key] = undefined
    })
  }
})
</script>

<style scoped>
.data-table {
  height: 100%;
}

.toolbar-card {
  margin-bottom: 16px;
}

.table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filters {
  display: flex;
  gap: 12px;
  align-items: center;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header h3 {
  margin: 0;
  color: #303133;
}

.table-stats {
  display: flex;
  gap: 8px;
}

.table-actions {
  display: flex;
  gap: 4px;
  justify-content: center;
  align-items: center;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.empty-state {
  padding: 40px 0;
}

@media (max-width: 768px) {
  .table-toolbar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .toolbar-left {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filters {
    flex-wrap: wrap;
  }
  
  .table-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>
