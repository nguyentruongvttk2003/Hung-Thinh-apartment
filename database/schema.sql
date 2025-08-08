-- Tạo database cho hệ thống quản lý chung cư Hưng Thịnh
CREATE DATABASE IF NOT EXISTS hung_thinh_apartment
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE hung_thinh_apartment;

-- Bảng users (người dùng)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    role ENUM('admin', 'resident', 'accountant', 'technician') DEFAULT 'resident',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_role_status (role, status)
);

-- Bảng apartments (căn hộ)
CREATE TABLE apartments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    apartment_number VARCHAR(50) UNIQUE NOT NULL,
    block VARCHAR(10) NULL,
    floor INT NOT NULL,
    room_number INT NOT NULL,
    area DECIMAL(8,2) NOT NULL,
    bedrooms INT DEFAULT 1,
    type ENUM('studio', '1BR', '2BR', '3BR', 'penthouse') DEFAULT '1BR',
    status ENUM('occupied', 'vacant', 'maintenance', 'reserved') DEFAULT 'vacant',
    owner_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_block_floor (block, floor),
    INDEX idx_status (status)
);

-- Bảng residents (cư dân)
CREATE TABLE residents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    apartment_id BIGINT UNSIGNED NOT NULL,
    relationship ENUM('owner', 'tenant', 'family_member', 'domestic_worker') DEFAULT 'tenant',
    move_in_date DATE NOT NULL,
    move_out_date DATE NULL,
    status ENUM('active', 'inactive', 'moved_out') DEFAULT 'active',
    is_primary_contact BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_apartment (user_id, apartment_id),
    INDEX idx_apartment_status (apartment_id, status)
);

-- Bảng notifications (thông báo)
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('general', 'maintenance', 'payment', 'event', 'emergency') DEFAULT 'general',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    scope ENUM('all', 'block', 'floor', 'apartment', 'specific') DEFAULT 'all',
    target_scope JSON NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'sent', 'cancelled') DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type_status (type, status),
    INDEX idx_scheduled_at (scheduled_at)
);

-- Bảng notification_recipients (người nhận thông báo)
CREATE TABLE notification_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    delivery_status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    delivery_error TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_notification_user (notification_id, user_id),
    INDEX idx_user_read_at (user_id, read_at)
);

-- Bảng feedbacks (phản ánh)
CREATE TABLE feedbacks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    apartment_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('complaint', 'suggestion', 'maintenance_request', 'general') DEFAULT 'general',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('pending', 'in_progress', 'resolved', 'closed', 'rejected') DEFAULT 'pending',
    assigned_to BIGINT UNSIGNED NULL,
    assigned_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    resolution_notes TEXT NULL,
    rating INT NULL,
    rating_comment TEXT NULL,
    attachments JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type_status (type, status),
    INDEX idx_assigned_to_status (assigned_to, status)
);

-- Bảng invoices (hóa đơn)
CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    apartment_id BIGINT UNSIGNED NOT NULL,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,
    due_date DATE NOT NULL,
    management_fee DECIMAL(12,2) DEFAULT 0,
    electricity_fee DECIMAL(12,2) DEFAULT 0,
    water_fee DECIMAL(12,2) DEFAULT 0,
    parking_fee DECIMAL(12,2) DEFAULT 0,
    other_fees DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending', 'partial', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_apartment_billing (apartment_id, billing_period_start),
    INDEX idx_status_due_date (status, due_date)
);

-- Bảng payments (thanh toán)
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'qr_code', 'credit_card', 'e_wallet') DEFAULT 'cash',
    status ENUM('pending', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100) NULL,
    payment_details JSON NULL,
    paid_at TIMESTAMP NULL,
    processed_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoice_status (invoice_id, status),
    INDEX idx_user_paid_at (user_id, paid_at)
);

-- Bảng devices (thiết bị)
CREATE TABLE devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    device_code VARCHAR(50) UNIQUE NOT NULL,
    category ENUM('elevator', 'generator', 'water_pump', 'air_conditioner', 'lighting', 'security', 'other') DEFAULT 'other',
    location VARCHAR(255) NOT NULL,
    brand VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    installation_date DATE NOT NULL,
    warranty_expiry DATE NULL,
    status ENUM('active', 'inactive', 'maintenance', 'broken') DEFAULT 'active',
    specifications TEXT NULL,
    notes TEXT NULL,
    responsible_technician BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (responsible_technician) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category_status (category, status),
    INDEX idx_responsible_technician (responsible_technician)
);

-- Bảng maintenances (bảo trì)
CREATE TABLE maintenances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('preventive', 'corrective', 'emergency') DEFAULT 'preventive',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    scheduled_date DATE NOT NULL,
    scheduled_time TIME NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    assigned_technician BIGINT UNSIGNED NULL,
    work_performed TEXT NULL,
    parts_replaced TEXT NULL,
    cost DECIMAL(12,2) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_technician) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_device_status (device_id, status),
    INDEX idx_scheduled_date_status (scheduled_date, status),
    INDEX idx_assigned_technician (assigned_technician)
);

-- Bảng events (sự kiện)
CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('meeting', 'maintenance', 'power_outage', 'water_outage', 'social_event', 'emergency') DEFAULT 'meeting',
    scope ENUM('all', 'block', 'floor', 'apartment', 'specific') DEFAULT 'all',
    target_scope JSON NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    location VARCHAR(255) NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type_status (type, status),
    INDEX idx_start_time_end_time (start_time, end_time)
);

-- Bảng votes (biểu quyết)
CREATE TABLE votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('general_meeting', 'budget_approval', 'rule_change', 'facility_upgrade', 'other') DEFAULT 'general_meeting',
    scope ENUM('all', 'block', 'floor', 'apartment') DEFAULT 'all',
    target_scope JSON NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('draft', 'active', 'closed', 'cancelled') DEFAULT 'draft',
    require_quorum BOOLEAN DEFAULT TRUE,
    quorum_percentage INT DEFAULT 50,
    created_by BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type_status (type, status),
    INDEX idx_start_time_end_time (start_time, end_time)
);

-- Bảng vote_options (tùy chọn biểu quyết)
CREATE TABLE vote_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vote_id BIGINT UNSIGNED NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    description TEXT NULL,
    vote_count INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (vote_id) REFERENCES votes(id) ON DELETE CASCADE,
    INDEX idx_vote_sort_order (vote_id, sort_order)
);

-- Bảng vote_responses (phản hồi biểu quyết)
CREATE TABLE vote_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vote_id BIGINT UNSIGNED NOT NULL,
    vote_option_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    voted_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (vote_id) REFERENCES votes(id) ON DELETE CASCADE,
    FOREIGN KEY (vote_option_id) REFERENCES vote_options(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote_user (vote_id, user_id),
    INDEX idx_vote_option (vote_id, vote_option_id)
);

-- Bảng migrations (để Laravel theo dõi migrations)
CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
); 