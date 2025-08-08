# Hệ thống Thông tin Quản lý Chung cư Hưng Thịnh

## Giới thiệu
Hệ thống được xây dựng nhằm số hóa toàn bộ nghiệp vụ quản lý của Ban Quản trị chung cư, bao gồm quản lý cư dân, thông báo, phí dịch vụ, thiết bị bảo trì và cung cấp kênh tương tác trực tuyến.

## Kiến trúc hệ thống
- **Backend**: Laravel RESTful API
- **Frontend Web Admin**: Vue.js
- **Mobile App**: Android (Kotlin) - sẽ phát triển sau
- **Database**: MySQL

## Cấu trúc dự án
```
hung-thinh-apartment/
├── backend/                 # Laravel API
├── frontend/               # Vue.js Admin Panel
├── mobile/                 # Android App (sau này)
├── database/              # SQL scripts và migrations
└── docs/                  # Tài liệu hệ thống
```

## Tính năng chính
### Ban Quản trị
- Quản lý cư dân và căn hộ
- Gửi thông báo
- Theo dõi phản ánh
- Quản lý hóa đơn và tài chính
- Quản lý thiết bị và bảo trì

### Cư dân (Mobile App)
- Xem thông tin cá nhân
- Nhận thông báo
- Gửi phản ánh
- Thanh toán online
- Biểu quyết điện tử

### Kế toán/Kỹ thuật viên
- Cập nhật chỉ số điện/nước
- Tạo hóa đơn
- Xử lý bảo trì

## Cài đặt và chạy
1. Clone repository
2. Cài đặt backend Laravel
3. Cài đặt frontend Vue.js
4. Cấu hình database
5. Chạy migrations và seeders

## Yêu cầu hệ thống
- PHP 8.1+
- Node.js 16+
- MySQL 8.0+
- Composer
- npm/yarn 