// User types
export interface User {
  id: number
  name: string
  email: string
  phone: string
  avatar?: string
  role: 'admin' | 'resident' | 'technician' | 'accountant'
  status: 'active' | 'inactive'
  created_at: string
  updated_at: string
}

// Apartment types
export interface Apartment {
  id: number
  apartment_number: string
  block: string
  floor: number
  type: 'studio' | '1br' | '2br' | '3br' | '4br'
  area: number
  status: 'occupied' | 'vacant' | 'maintenance'
  description?: string
  owner_id: number
  owner?: User
  residents?: User[]
  created_at: string
  updated_at: string
}

// Resident types
export interface Resident {
  id: number
  name: string
  phone: string
  email: string
  relationship: string
  is_owner: boolean
  apartment_id: number
  created_at: string
  updated_at: string
}

// Notification types
export interface Notification {
  id: number
  title: string
  content: string
  type: 'general' | 'maintenance' | 'payment' | 'event'
  priority: 'low' | 'medium' | 'high'
  created_by: number
  created_at: string
  updated_at: string
}

// Feedback types
export interface Feedback {
  id: number
  title: string
  description: string
  type: 'complaint' | 'suggestion' | 'maintenance'
  status: 'pending' | 'in_progress' | 'resolved' | 'closed'
  priority: 'low' | 'medium' | 'high'
  created_by: number
  assigned_to?: number
  assigned_technician?: User
  assigned_at?: string
  created_at: string
  updated_at: string
}

// Invoice types
export interface Invoice {
  id: number
  invoice_number: string
  apartment_id: number
  apartment?: Apartment
  billing_period_start: string
  billing_period_end: string
  due_date: string
  management_fee: number
  electricity_fee: number
  water_fee: number
  parking_fee: number
  other_fees: number
  total_amount: number
  paid_amount: number
  status: 'pending' | 'partial' | 'paid' | 'overdue' | 'cancelled'
  notes?: string
  created_by: number
  creator?: User
  created_at: string
  updated_at: string
}

// Payment types
export interface Payment {
  id: number
  payment_number: string
  invoice_id: number
  invoice?: Invoice
  user_id: number
  user?: User
  amount: number
  payment_method: 'cash' | 'bank_transfer' | 'qr_code' | 'credit_card' | 'e_wallet'
  status: 'pending' | 'completed' | 'failed' | 'cancelled' | 'refunded'
  transaction_id?: string
  payment_details?: any[] | null
  paid_at?: string
  processed_by?: number
  processor?: User
  notes?: string
  created_at: string
  updated_at: string
}

// Device types
export interface Device {
  id: number
  name: string
  device_code: string
  category: 'elevator' | 'generator' | 'water_pump' | 'air_conditioner' | 'lighting' | 'security' | 'other'
  location: string
  brand?: string
  model?: string
  installation_date: string
  warranty_expiry?: string
  status: 'active' | 'inactive' | 'maintenance' | 'broken'
  specifications?: string
  notes?: string
  responsible_technician?: number
  responsible_technician_info?: User
  created_at: string
  updated_at: string
}

// Maintenance types
export interface Maintenance {
  id: number
  device_id: number
  device_name?: string
  technician_name?: string
  title: string
  description: string
  type: 'preventive' | 'corrective' | 'emergency'
  status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled'
  priority?: 'low' | 'medium' | 'high' | 'urgent'
  scheduled_date: string
  completed_date?: string
  assigned_to?: string
  assigned_technician?: number
  cost?: number
  notes?: string
  created_at: string
  updated_at: string
}

// Event types
export interface Event {
  id: number
  title: string
  description: string
  type: 'meeting' | 'maintenance' | 'power_outage' | 'water_outage' | 'social_event' | 'emergency'
  scope: 'all' | 'block' | 'floor' | 'apartment' | 'specific'
  target_scope?: any
  start_time: string
  end_time?: string
  location?: string
  status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled'
  created_by: number
  notes?: string
  creator?: User
  created_at: string
  updated_at: string
}

// Vote types
export interface Vote {
  id: number
  title: string
  description: string
  type: 'general_meeting' | 'budget_approval' | 'rule_change' | 'facility_upgrade' | 'other'
  scope: 'all' | 'block' | 'floor' | 'apartment'
  target_scope?: any
  start_time: string
  end_time: string
  status: 'draft' | 'active' | 'closed' | 'cancelled'
  require_quorum: boolean
  quorum_percentage: number
  created_by: number
  notes?: string
  creator?: User
  created_at: string
  updated_at: string
  options?: VoteOption[]
}

export interface VoteOption {
  id: number
  vote_id: number
  option_text: string
  vote_count: number
  created_at: string
  updated_at: string
}

// API Response types
export interface ApiResponse<T> {
  data: T
  message: string
  success: boolean
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  message: string
  success: boolean
}

// Dashboard types
export interface DashboardStats {
  total_apartments: number
  total_residents: number
  total_revenue: number
  pending_feedbacks: number
  upcoming_maintenance: number
  active_notifications: number
}

export interface ChartData {
  labels: string[]
  datasets: {
    label: string
    data: number[]
    backgroundColor?: string[]
    borderColor?: string
    borderWidth?: number
  }[]
} 