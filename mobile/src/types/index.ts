// User types
export interface User {
  id: number;
  name: string;
  email: string;
  phone: string;
  avatar?: string;
  role: 'admin' | 'resident' | 'technician' | 'accountant';
  status: 'active' | 'inactive';
  apartmentId?: number;
  created_at: string;
  updated_at: string;
}

// Authentication types
export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthResponse {
  token: string;
  user: User;
  expires_in: number;
}

// Apartment types
export interface Apartment {
  id: number;
  apartment_number: string;
  block: string;
  floor: number;
  type: 'studio' | '1br' | '2br' | '3br' | '4br';
  area: number;
  status: 'occupied' | 'vacant' | 'maintenance';
  description?: string;
  owner_id: number;
  owner?: User;
  residents?: User[];
  created_at: string;
  updated_at: string;
}

// Resident types
export interface Resident {
  id: number;
  name: string;
  phone: string;
  email: string;
  relationship: string;
  is_owner: boolean;
  apartment_id: number;
  created_at: string;
  updated_at: string;
}

// Device types
export interface Device {
  id: number;
  name: string;
  type: 'elevator' | 'electrical' | 'water' | 'camera' | 'other';
  location: string;
  status: 'active' | 'maintenance' | 'broken';
  description?: string;
  last_maintenance: string;
  next_maintenance: string;
  assigned_to?: number;
  created_at: string;
  updated_at: string;
}

// Maintenance types
export interface Maintenance {
  id: number;
  device_id: number;
  device_name?: string;
  title: string;
  description: string;
  type: 'preventive' | 'corrective' | 'emergency';
  status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
  priority?: 'low' | 'medium' | 'high' | 'urgent';
  scheduled_date: string;
  completed_date?: string;
  assigned_to?: string;
  cost?: number;
  notes?: string;
  created_at: string;
  updated_at: string;
}

// Vote types
export interface Vote {
  id: number;
  title: string;
  description: string;
  start_date: string;
  end_date: string;
  endDate?: string; // Alias for end_date
  status: 'active' | 'closed' | 'cancelled';
  created_by: number;
  totalVotes?: number;
  created_at: string;
  updated_at: string;
  options?: VoteOption[];
}

export interface VoteOption {
  id: number;
  vote_id: number;
  option_text: string;
  vote_count: number;
  created_at: string;
  updated_at: string;
}

export interface VoteResponse {
  id: number;
  vote_id: number;
  option_id: number;
  user_id: number;
  created_at: string;
}

// API Response types
export interface ApiResponse<T> {
  data: T;
  message: string;
  success: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  currentPage?: number; // Alias for current_page
  last_page: number;
  totalPages?: number; // Alias for last_page
  per_page: number;
  total: number;
  message: string;
  success: boolean;
  refresh?: boolean;
}

// Navigation types
export type RootStackParamList = {
  Login: undefined;
  TabNavigator: undefined;
  Home: undefined;
  Apartments: undefined;
  ApartmentDetail: { apartmentId: number };
  Maintenance: undefined;
  MaintenanceDetail: { maintenanceId: number };
  Votes: undefined;
  VoteDetail: { voteId: number };
  Profile: undefined;
  Settings: undefined;
};

export type TabParamList = {
  Home: undefined;
  Apartments: undefined;
  Maintenance: undefined;
  Votes: undefined;
  Profile: undefined;
};
