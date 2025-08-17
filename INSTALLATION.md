# Hướng dẫn cài đặt hệ thống quản lý chung cư Hưng Thịnh

## Yêu cầu hệ thống

### Backend (Laravel)
- PHP 8.1 hoặc cao hơn
- Composer
- MySQL 8.0 hoặc cao hơn
- Node.js 16+ (cho frontend)

### Frontend (Vue.js)
- Node.js 16+ 
- npm hoặc yarn

## Cài đặt Backend

### 1. Cài đặt dependencies
```bash
cd backend
composer install
```

### 2. Cấu hình môi trường
```bash
# Copy file env.example thành .env
cp env.example .env

# Tạo application key
php artisan key:generate

# Tạo JWT secret
php artisan jwt:secret
```

### 3. Cấu hình database
Chỉnh sửa file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hung_thinh_apartment
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Tạo database
```sql
-- Chạy file database/schema.sql để tạo database và bảng
mysql -u your_username -p < database/schema.sql

-- Import dữ liệu mẫu (tùy chọn)
mysql -u your_username -p hung_thinh_apartment < database/sample_data.sql
```

### 5. Chạy migrations (nếu không dùng schema.sql)
```bash
php artisan migrate
php artisan db:seed
```

### 6. Tạo storage link
```bash
php artisan storage:link
```

### 7. Cấu hình CORS
Chỉnh sửa file `config/cors.php` để cho phép frontend truy cập:
```php
'allowed_origins' => ['http://localhost:3000', 'http://localhost:8080'],
```

### 8. Chạy server
```bash
php artisan serve
```

Server sẽ chạy tại: http://localhost:8000

## Cài đặt Frontend

### 1. Cài đặt dependencies
```bash
cd frontend
npm install
```

### 2. Cấu hình API endpoint
Chỉnh sửa file `.env`:
```env
VITE_API_URL=http://localhost:8000/api
```

### 3. Chạy development server
```bash
npm run dev
```

Frontend sẽ chạy tại: http://localhost:3000

## Tài khoản mẫu

Sau khi import dữ liệu mẫu, bạn có thể đăng nhập với các tài khoản sau:

### Admin
- Email: admin@hungthinh.com
- Password: password

### Kế toán
- Email: ketoan@hungthinh.com
- Password: password

### Kỹ thuật viên
- Email: kythuat@hungthinh.com
- Password: password
- Email: baotri@hungthinh.com
- Password: password

### Cư dân
- Email: nguyenvana@gmail.com
- Password: password
- Email: tranthib@gmail.com
- Password: password
- Email: levanc@gmail.com
- Password: password
- Email: phamthid@gmail.com
- Password: password
- Email: hoangvane@gmail.com
- Password: password
- Email: vuthif@gmail.com
- Password: password

## API Endpoints

### Authentication
- `POST /api/login` - Đăng nhập
- `POST /api/register` - Đăng ký
- `POST /api/logout` - Đăng xuất
- `POST /api/refresh` - Làm mới token
- `GET /api/profile` - Thông tin profile

### Apartments
- `GET /api/apartments` - Danh sách căn hộ
- `POST /api/apartments` - Tạo căn hộ mới
- `GET /api/apartments/{id}` - Chi tiết căn hộ
- `PUT /api/apartments/{id}` - Cập nhật căn hộ
- `DELETE /api/apartments/{id}` - Xóa căn hộ

### Users
- `GET /api/users` - Danh sách người dùng
- `POST /api/users` - Tạo người dùng mới
- `GET /api/users/{id}` - Chi tiết người dùng
- `PUT /api/users/{id}` - Cập nhật người dùng
- `DELETE /api/users/{id}` - Xóa người dùng

### Notifications
- `GET /api/notifications` - Danh sách thông báo
- `POST /api/notifications` - Tạo thông báo mới
- `POST /api/notifications/{id}/send` - Gửi thông báo
- `GET /api/notifications/received` - Thông báo đã nhận

### Feedbacks
- `GET /api/feedbacks` - Danh sách phản ánh
- `POST /api/feedbacks` - Tạo phản ánh mới
- `POST /api/feedbacks/{id}/assign` - Phân công xử lý
- `POST /api/feedbacks/{id}/resolve` - Giải quyết phản ánh

### Invoices
- `GET /api/invoices` - Danh sách hóa đơn
- `POST /api/invoices` - Tạo hóa đơn mới
- `GET /api/invoices/{id}` - Chi tiết hóa đơn
- `POST /api/invoices/bulk-create` - Tạo hóa đơn hàng loạt

### Payments
- `GET /api/payments` - Danh sách thanh toán
- `POST /api/payments` - Tạo thanh toán mới
- `POST /api/payments/{id}/process` - Xử lý thanh toán

### Devices
- `GET /api/devices` - Danh sách thiết bị
- `POST /api/devices` - Tạo thiết bị mới
- `GET /api/devices/{id}` - Chi tiết thiết bị
- `PUT /api/devices/{id}` - Cập nhật thiết bị

### Maintenances
- `GET /api/maintenances` - Danh sách bảo trì
- `POST /api/maintenances` - Tạo lịch bảo trì
- `POST /api/maintenances/{id}/start` - Bắt đầu bảo trì
- `POST /api/maintenances/{id}/complete` - Hoàn thành bảo trì

### Events
- `GET /api/events` - Danh sách sự kiện
- `POST /api/events` - Tạo sự kiện mới
- `GET /api/events/upcoming` - Sự kiện sắp tới

### Votes
- `GET /api/votes` - Danh sách biểu quyết
- `POST /api/votes` - Tạo biểu quyết mới
- `POST /api/votes/{id}/activate` - Kích hoạt biểu quyết
- `POST /api/votes/{id}/vote` - Bỏ phiếu
- `GET /api/votes/{id}/results` - Kết quả biểu quyết

## Cấu trúc thư mục

```
hung-thinh-apartment/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/  # API Controllers
│   │   ├── Models/                # Eloquent Models
│   │   └── Services/              # Business Logic
│   ├── database/
│   │   ├── migrations/            # Database migrations
│   │   └── seeders/               # Database seeders
│   ├── routes/
│   │   └── api.php                # API routes
│   └── config/                    # Configuration files
├── frontend/               # Vue.js Admin Panel
│   ├── src/
│   │   ├── components/            # Vue components
│   │   ├── views/                 # Page views
│   │   ├── router/                # Vue router
│   │   └── store/                 # Vuex store
│   └── public/                    # Static files
├── database/              # SQL scripts
│   ├── schema.sql                # Database schema
│   └── sample_data.sql           # Sample data
└── docs/                  # Documentation
```

## Troubleshooting

### Lỗi CORS
- Kiểm tra cấu hình CORS trong `config/cors.php`
- Đảm bảo frontend URL được thêm vào `allowed_origins`

### Lỗi database connection
- Kiểm tra thông tin kết nối database trong `.env`
- Đảm bảo MySQL service đang chạy
- Kiểm tra quyền truy cập database

### Lỗi JWT
- Chạy `php artisan jwt:secret` để tạo JWT secret
- Kiểm tra cấu hình JWT trong `config/jwt.php`

### Lỗi storage
- Chạy `php artisan storage:link` để tạo symbolic link
- Kiểm tra quyền ghi trong thư mục `storage`

## Deployment

### Production
1. Cài đặt dependencies: `composer install --optimize-autoloader --no-dev`
2. Cấu hình environment: `APP_ENV=production`
3. Cache configuration: `php artisan config:cache`
4. Cache routes: `php artisan route:cache`
5. Cache views: `php artisan view:cache`
6. Optimize autoloader: `composer dump-autoload --optimize`

### Security
- Thay đổi tất cả mật khẩu mặc định
- Cấu hình HTTPS
- Bật firewall
- Cập nhật dependencies thường xuyên
- Backup database định kỳ

## Support

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra logs trong `storage/logs/`
2. Kiểm tra documentation
3. Tạo issue trên repository
4. Liên hệ support team 
 C:\Users\Hoang_Hai\OneDrive\Máy tính\Management Infomation Aparterment> cd "c:\Users\Hoang_Hai\OneDrive\Máy tính\Management Infomation Aparterment"
>> .\gradlew.bat :android-app:clean :android-app:assembleDebug