-- Dữ liệu mẫu cho hệ thống quản lý chung cư Hưng Thịnh

USE hung_thinh_apartment;

-- Thêm người dùng mẫu
INSERT INTO users (name, email, phone, role, status, password, created_at, updated_at) VALUES
-- Admin (password: admin123)
('Nguyễn Văn Admin', 'admin@hungthinh.com', '0901234567', 'admin', 'active', 'admin123', NOW(), NOW()),

-- Kế toán (password: ketoan123)
('Trần Thị Kế Toán', 'ketoan@hungthinh.com', '0901234568', 'accountant', 'active', 'ketoan123', NOW(), NOW()),

-- Kỹ thuật viên (password: kythuat123)
('Lê Văn Kỹ Thuật', 'kythuat@hungthinh.com', '0901234569', 'technician', 'active', 'kythuat123', NOW(), NOW()),
('Phạm Thị Bảo Trì', 'baotri@hungthinh.com', '0901234570', 'technician', 'active', 'baotri123', NOW(), NOW()),

-- Cư dân (password: 123456)
('Nguyễn Văn A', 'nguyenvana@gmail.com', '0901234571', 'resident', 'active', '123456', NOW(), NOW()),
('Trần Thị B', 'tranthib@gmail.com', '0901234572', 'resident', 'active', '123456', NOW(), NOW()),
('Lê Văn C', 'levanc@gmail.com', '0901234573', 'resident', 'active', '123456', NOW(), NOW()),
('Phạm Thị D', 'phamthid@gmail.com', '0901234574', 'resident', 'active', '123456', NOW(), NOW()),
('Hoàng Văn E', 'hoangvane@gmail.com', '0901234575', 'resident', 'active', '123456', NOW(), NOW()),
('Vũ Thị F', 'vuthif@gmail.com', '0901234576', 'resident', 'active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Thêm căn hộ mẫu
INSERT INTO apartments (apartment_number, block, floor, room_number, area, bedrooms, type, status, owner_id, description, created_at, updated_at) VALUES
('A1-101', 'A', 1, 101, 45.5, 1, '1BR', 'occupied', 5, 'Căn hộ 1 phòng ngủ, view hồ bơi', NOW(), NOW()),
('A1-102', 'A', 1, 102, 45.5, 1, '1BR', 'occupied', 6, 'Căn hộ 1 phòng ngủ, view hồ bơi', NOW(), NOW()),
('A1-201', 'A', 2, 201, 65.0, 2, '2BR', 'occupied', 7, 'Căn hộ 2 phòng ngủ, view thành phố', NOW(), NOW()),
('A1-202', 'A', 2, 202, 65.0, 2, '2BR', 'occupied', 8, 'Căn hộ 2 phòng ngủ, view thành phố', NOW(), NOW()),
('A1-301', 'A', 3, 301, 85.0, 3, '3BR', 'occupied', 9, 'Căn hộ 3 phòng ngủ, view toàn cảnh', NOW(), NOW()),
('A1-302', 'A', 3, 302, 85.0, 3, '3BR', 'vacant', NULL, 'Căn hộ 3 phòng ngủ, view toàn cảnh', NOW(), NOW()),
('B1-101', 'B', 1, 101, 50.0, 1, '1BR', 'occupied', 10, 'Căn hộ 1 phòng ngủ, view vườn', NOW(), NOW()),
('B1-102', 'B', 1, 102, 50.0, 1, '1BR', 'vacant', NULL, 'Căn hộ 1 phòng ngủ, view vườn', NOW(), NOW()),
('B1-201', 'B', 2, 201, 70.0, 2, '2BR', 'maintenance', NULL, 'Căn hộ 2 phòng ngủ, đang bảo trì', NOW(), NOW()),
('B1-202', 'B', 2, 202, 70.0, 2, '2BR', 'reserved', NULL, 'Căn hộ 2 phòng ngủ, đã đặt trước', NOW(), NOW());

-- Thêm cư dân vào căn hộ
INSERT INTO residents (user_id, apartment_id, relationship, move_in_date, status, is_primary_contact, created_at, updated_at) VALUES
(5, 1, 'owner', '2023-01-15', 'active', TRUE, NOW(), NOW()),
(6, 2, 'owner', '2023-02-01', 'active', TRUE, NOW(), NOW()),
(7, 3, 'owner', '2023-01-20', 'active', TRUE, NOW(), NOW()),
(8, 4, 'owner', '2023-02-10', 'active', TRUE, NOW(), NOW()),
(9, 5, 'owner', '2023-01-25', 'active', TRUE, NOW(), NOW()),
(10, 7, 'owner', '2023-02-15', 'active', TRUE, NOW(), NOW());

-- Thêm thiết bị mẫu
INSERT INTO devices (name, device_code, category, location, brand, model, installation_date, status, responsible_technician, created_at, updated_at) VALUES
('Thang máy A1', 'ELEV-A1-01', 'elevator', 'Block A, Tầng 1', 'Mitsubishi', 'NexWay-S', '2022-12-01', 'active', 3, NOW(), NOW()),
('Thang máy A2', 'ELEV-A1-02', 'elevator', 'Block A, Tầng 1', 'Mitsubishi', 'NexWay-S', '2022-12-01', 'active', 3, NOW(), NOW()),
('Máy bơm nước', 'PUMP-WATER-01', 'water_pump', 'Tầng hầm', 'Grundfos', 'CR 10-5', '2022-12-01', 'active', 4, NOW(), NOW()),
('Máy phát điện', 'GEN-POWER-01', 'generator', 'Tầng hầm', 'Cummins', 'C1100D5', '2022-12-01', 'active', 3, NOW(), NOW()),
('Camera an ninh', 'CAM-SEC-01', 'security', 'Sảnh chính', 'Hikvision', 'DS-2CD2142FWD-I', '2022-12-01', 'active', 4, NOW(), NOW()),
('Đèn chiếu sáng', 'LIGHT-01', 'lighting', 'Sảnh chính', 'Philips', 'LED Panel', '2022-12-01', 'active', 4, NOW(), NOW());

-- Thêm hóa đơn mẫu
INSERT INTO invoices (invoice_number, apartment_id, billing_period_start, billing_period_end, due_date, management_fee, electricity_fee, water_fee, parking_fee, other_fees, total_amount, paid_amount, status, created_by, created_at, updated_at) VALUES
('INV-2024-001', 1, '2024-01-01', '2024-01-31', '2024-02-15', 500000, 350000, 150000, 200000, 0, 1200000, 1200000, 'paid', 2, NOW(), NOW()),
('INV-2024-002', 2, '2024-01-01', '2024-01-31', '2024-02-15', 500000, 320000, 140000, 200000, 0, 1160000, 800000, 'partial', 2, NOW(), NOW()),
('INV-2024-003', 3, '2024-01-01', '2024-01-31', '2024-02-15', 500000, 450000, 180000, 200000, 0, 1330000, 0, 'pending', 2, NOW(), NOW()),
('INV-2024-004', 4, '2024-01-01', '2024-01-31', '2024-02-15', 500000, 420000, 160000, 200000, 0, 1280000, 0, 'overdue', 2, NOW(), NOW()),
('INV-2024-005', 5, '2024-01-01', '2024-01-31', '2024-02-15', 500000, 550000, 200000, 200000, 0, 1450000, 1450000, 'paid', 2, NOW(), NOW()),
('INV-2024-006', 7, '2024-01-01', '2024-01-31', '2024-02-15', 500000, 380000, 160000, 200000, 0, 1240000, 0, 'pending', 2, NOW(), NOW());

-- Thêm thanh toán mẫu
INSERT INTO payments (payment_number, invoice_id, user_id, amount, payment_method, status, paid_at, processed_by, created_at, updated_at) VALUES
('PAY-2024-001', 1, 5, 1200000, 'bank_transfer', 'completed', '2024-02-10 10:30:00', 2, NOW(), NOW()),
('PAY-2024-002', 2, 6, 800000, 'qr_code', 'completed', '2024-02-12 14:20:00', 2, NOW(), NOW()),
('PAY-2024-003', 5, 9, 1450000, 'cash', 'completed', '2024-02-08 09:15:00', 2, NOW(), NOW());

-- Thêm thông báo mẫu
INSERT INTO notifications (title, content, type, priority, scope, created_by, status, sent_at, created_at, updated_at) VALUES
('Thông báo cắt điện bảo trì', 'Kính gửi quý cư dân, chúng tôi sẽ tiến hành cắt điện bảo trì hệ thống điện từ 22:00 ngày 15/02/2024 đến 06:00 ngày 16/02/2024. Mong quý cư dân chuẩn bị trước.', 'maintenance', 'high', 'all', 1, 'sent', NOW(), NOW(), NOW()),
('Thông báo họp cư dân tháng 2', 'Kính mời quý cư dân tham dự cuộc họp cư dân thường niên tháng 2/2024 vào lúc 19:00 ngày 20/02/2024 tại sảnh chính.', 'event', 'normal', 'all', 1, 'sent', NOW(), NOW(), NOW()),
('Thông báo về phí dịch vụ', 'Kính gửi quý cư dân, từ tháng 3/2024, phí dịch vụ sẽ được điều chỉnh tăng 5% theo quy định mới. Chi tiết xem tại bảng thông báo.', 'payment', 'normal', 'all', 1, 'sent', NOW(), NOW(), NOW());

-- Thêm phản ánh mẫu
INSERT INTO feedbacks (user_id, apartment_id, title, content, type, priority, status, created_at, updated_at) VALUES
(5, 1, 'Đèn hành lang bị hỏng', 'Đèn hành lang tầng 1 bị hỏng, cần sửa gấp để đảm bảo an toàn.', 'maintenance_request', 'high', 'resolved', NOW(), NOW()),
(6, 2, 'Đề xuất lắp thêm camera', 'Đề xuất lắp thêm camera an ninh tại khu vực gửi xe để tăng cường bảo mật.', 'suggestion', 'normal', 'pending', NOW(), NOW()),
(7, 3, 'Nước rò rỉ từ trần', 'Phát hiện nước rò rỉ từ trần phòng khách, cần kiểm tra và sửa chữa.', 'complaint', 'urgent', 'in_progress', NOW(), NOW()),
(8, 4, 'Tiếng ồn từ máy bơm', 'Máy bơm nước phát ra tiếng ồn lớn vào ban đêm, ảnh hưởng đến giấc ngủ.', 'complaint', 'high', 'pending', NOW(), NOW());

-- Thêm lịch bảo trì mẫu
INSERT INTO maintenances (device_id, title, description, type, priority, status, scheduled_date, scheduled_time, assigned_technician, created_at, updated_at) VALUES
(1, 'Bảo trì thang máy A1', 'Kiểm tra và bảo trì định kỳ thang máy A1', 'preventive', 'normal', 'scheduled', '2024-02-20', '08:00:00', 3, NOW(), NOW()),
(2, 'Bảo trì thang máy A2', 'Kiểm tra và bảo trì định kỳ thang máy A2', 'preventive', 'normal', 'scheduled', '2024-02-21', '08:00:00', 3, NOW(), NOW()),
(3, 'Kiểm tra máy bơm nước', 'Kiểm tra hoạt động và bảo trì máy bơm nước', 'preventive', 'normal', 'scheduled', '2024-02-22', '09:00:00', 4, NOW(), NOW()),
(4, 'Sửa chữa máy phát điện', 'Máy phát điện có tiếng ồn bất thường, cần kiểm tra và sửa chữa', 'corrective', 'high', 'in_progress', '2024-02-15', '10:00:00', 3, NOW(), NOW());

-- Thêm sự kiện mẫu
INSERT INTO events (title, description, type, scope, start_time, end_time, location, status, created_by, created_at, updated_at) VALUES
('Họp cư dân tháng 2/2024', 'Cuộc họp cư dân thường niên để thảo luận các vấn đề chung và kế hoạch phát triển', 'meeting', 'all', '2024-02-20 19:00:00', '2024-02-20 21:00:00', 'Sảnh chính tầng 1', 'scheduled', 1, NOW(), NOW()),
('Cắt điện bảo trì hệ thống', 'Cắt điện để bảo trì hệ thống điện chung', 'maintenance', 'all', '2024-02-15 22:00:00', '2024-02-16 06:00:00', 'Toàn bộ chung cư', 'scheduled', 1, NOW(), NOW()),
('Tiệc tân niên 2024', 'Tiệc tân niên chào đón năm mới 2024', 'social_event', 'all', '2024-01-15 18:00:00', '2024-01-15 22:00:00', 'Sân vườn chung cư', 'completed', 1, NOW(), NOW());

-- Thêm biểu quyết mẫu
INSERT INTO votes (title, description, type, scope, start_time, end_time, status, require_quorum, quorum_percentage, created_by, created_at, updated_at) VALUES
('Biểu quyết tăng phí dịch vụ', 'Biểu quyết về việc tăng phí dịch vụ 5% từ tháng 3/2024', 'budget_approval', 'all', '2024-02-01 00:00:00', '2024-02-15 23:59:59', 'active', TRUE, 50, 1, NOW(), NOW()),
('Biểu quyết lắp thêm camera an ninh', 'Biểu quyết về việc lắp thêm camera an ninh tại khu vực gửi xe', 'facility_upgrade', 'all', '2024-02-10 00:00:00', '2024-02-25 23:59:59', 'active', TRUE, 50, 1, NOW(), NOW());

-- Thêm tùy chọn biểu quyết
INSERT INTO vote_options (vote_id, option_text, description, sort_order, created_at, updated_at) VALUES
(1, 'Đồng ý', 'Đồng ý tăng phí dịch vụ 5%', 1, NOW(), NOW()),
(1, 'Không đồng ý', 'Không đồng ý tăng phí dịch vụ', 2, NOW(), NOW()),
(2, 'Đồng ý', 'Đồng ý lắp thêm camera an ninh', 1, NOW(), NOW()),
(2, 'Không đồng ý', 'Không đồng ý lắp thêm camera an ninh', 2, NOW(), NOW());

-- Thêm phản hồi biểu quyết mẫu
INSERT INTO vote_responses (vote_id, vote_option_id, user_id, voted_at, created_at, updated_at) VALUES
(1, 1, 5, NOW(), NOW(), NOW()),
(1, 1, 6, NOW(), NOW(), NOW()),
(1, 2, 7, NOW(), NOW(), NOW()),
(2, 1, 5, NOW(), NOW(), NOW()),
(2, 1, 6, NOW(), NOW(), NOW()),
(2, 1, 7, NOW(), NOW(), NOW()),
(2, 1, 8, NOW(), NOW(), NOW());

-- Cập nhật số phiếu bầu cho các tùy chọn
UPDATE vote_options SET vote_count = 2 WHERE id = 1; -- Đồng ý tăng phí
UPDATE vote_options SET vote_count = 1 WHERE id = 2; -- Không đồng ý tăng phí
UPDATE vote_options SET vote_count = 4 WHERE id = 3; -- Đồng ý lắp camera
UPDATE vote_options SET vote_count = 0 WHERE id = 4; -- Không đồng ý lắp camera

-- Thêm người nhận thông báo mẫu
INSERT INTO notification_recipients (notification_id, user_id, read_at, sent_at, delivery_status, created_at, updated_at) VALUES
(1, 5, NOW(), NOW(), 'delivered', NOW(), NOW()),
(1, 6, NOW(), NOW(), 'delivered', NOW(), NOW()),
(1, 7, NOW(), NOW(), 'delivered', NOW(), NOW()),
(1, 8, NULL, NOW(), 'sent', NOW(), NOW()),
(1, 9, NULL, NOW(), 'sent', NOW(), NOW()),
(1, 10, NULL, NOW(), 'sent', NOW(), NOW()),
(2, 5, NOW(), NOW(), 'delivered', NOW(), NOW()),
(2, 6, NOW(), NOW(), 'delivered', NOW(), NOW()),
(2, 7, NULL, NOW(), 'sent', NOW(), NOW()),
(2, 8, NULL, NOW(), 'sent', NOW(), NOW()),
(2, 9, NULL, NOW(), 'sent', NOW(), NOW()),
(2, 10, NULL, NOW(), 'sent', NOW(), NOW()),
(3, 5, NULL, NOW(), 'sent', NOW(), NOW()),
(3, 6, NULL, NOW(), 'sent', NOW(), NOW()),
(3, 7, NULL, NOW(), 'sent', NOW(), NOW()),
(3, 8, NULL, NOW(), 'sent', NOW(), NOW()),
(3, 9, NULL, NOW(), 'sent', NOW(), NOW()),
(3, 10, NULL, NOW(), 'sent', NOW(), NOW()); 