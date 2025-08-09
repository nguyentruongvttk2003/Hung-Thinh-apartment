<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Resident;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Feedback;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Device;
use App\Models\Maintenance;
use App\Models\Event;
use App\Models\Vote;
use App\Models\VoteOption;
use App\Models\VoteResponse;

class CompleteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ (theo thứ tự foreign key)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        DB::table('vote_responses')->delete();
        DB::table('vote_options')->delete();
        DB::table('votes')->delete();
        DB::table('events')->delete();
        DB::table('maintenances')->delete();
        DB::table('devices')->delete();
        DB::table('payments')->delete();
        DB::table('invoices')->delete();
        DB::table('feedbacks')->delete();
        DB::table('notification_recipients')->delete();
        DB::table('notifications')->delete();
        DB::table('residents')->delete();
        DB::table('apartments')->delete();
        DB::table('users')->delete();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Tạo Users (15 users)
        $users = [
            [
                'name' => 'Admin Hệ thống',
                'email' => 'admin@apartment.com',
                'phone' => '0901234567',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Kế toán Nguyễn Thị Lan',
                'email' => 'accountant@apartment.com',
                'phone' => '0901234568',
                'role' => 'accountant',
                'status' => 'active',
                'password' => Hash::make('accountant123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Kỹ thuật viên Trần Văn Nam',
                'email' => 'technician@apartment.com',
                'phone' => '0901234569',
                'role' => 'technician',
                'status' => 'active',
                'password' => Hash::make('technician123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Nguyễn Văn An',
                'email' => 'resident1@example.com',
                'phone' => '0987654321',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Trần Thị Bình',
                'email' => 'resident2@example.com',
                'phone' => '0987654322',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Lê Văn Cường',
                'email' => 'resident3@example.com',
                'phone' => '0987654323',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Phạm Thị Dung',
                'email' => 'resident4@example.com',
                'phone' => '0987654324',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Hoàng Văn Em',
                'email' => 'resident5@example.com',
                'phone' => '0987654325',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Vũ Thị Phương',
                'email' => 'resident6@example.com',
                'phone' => '0987654326',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Đặng Văn Giang',
                'email' => 'resident7@example.com',
                'phone' => '0987654327',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bùi Thị Hoa',
                'email' => 'resident8@example.com',
                'phone' => '0987654328',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ngô Văn Ích',
                'email' => 'resident9@example.com',
                'phone' => '0987654329',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Cao Thị Kim',
                'email' => 'resident10@example.com',
                'phone' => '0987654330',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Đinh Văn Long',
                'email' => 'resident11@example.com',
                'phone' => '0987654331',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Lý Thị Mai',
                'email' => 'resident12@example.com',
                'phone' => '0987654332',
                'role' => 'resident',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::create($userData);
        }

        // Tạo Apartments (12 căn hộ)
        $apartments = [
            ['apartment_number' => 'A1-101', 'block' => 'A1', 'floor' => 1, 'room_number' => 101, 'area' => 65.5, 'bedrooms' => 2, 'type' => '2BR', 'status' => 'occupied', 'owner_id' => $createdUsers[3]->id],
            ['apartment_number' => 'A1-102', 'block' => 'A1', 'floor' => 1, 'room_number' => 102, 'area' => 75.0, 'bedrooms' => 2, 'type' => '2BR', 'status' => 'occupied', 'owner_id' => $createdUsers[4]->id],
            ['apartment_number' => 'A1-201', 'block' => 'A1', 'floor' => 2, 'room_number' => 201, 'area' => 65.5, 'bedrooms' => 2, 'type' => '2BR', 'status' => 'occupied', 'owner_id' => $createdUsers[5]->id],
            ['apartment_number' => 'A1-202', 'block' => 'A1', 'floor' => 2, 'room_number' => 202, 'area' => 75.0, 'bedrooms' => 2, 'type' => '2BR', 'status' => 'vacant'],
            ['apartment_number' => 'B1-101', 'block' => 'B1', 'floor' => 1, 'room_number' => 101, 'area' => 85.0, 'bedrooms' => 3, 'type' => '3BR', 'status' => 'occupied', 'owner_id' => $createdUsers[6]->id],
            ['apartment_number' => 'B1-102', 'block' => 'B1', 'floor' => 1, 'room_number' => 102, 'area' => 45.5, 'bedrooms' => 1, 'type' => '1BR', 'status' => 'occupied', 'owner_id' => $createdUsers[7]->id],
            ['apartment_number' => 'B1-201', 'block' => 'B1', 'floor' => 2, 'room_number' => 201, 'area' => 85.0, 'bedrooms' => 3, 'type' => '3BR', 'status' => 'occupied', 'owner_id' => $createdUsers[8]->id],
            ['apartment_number' => 'B1-202', 'block' => 'B1', 'floor' => 2, 'room_number' => 202, 'area' => 45.5, 'bedrooms' => 1, 'type' => '1BR', 'status' => 'maintenance'],
            ['apartment_number' => 'C1-101', 'block' => 'C1', 'floor' => 1, 'room_number' => 101, 'area' => 95.0, 'bedrooms' => 3, 'type' => '3BR', 'status' => 'occupied', 'owner_id' => $createdUsers[9]->id],
            ['apartment_number' => 'C1-102', 'block' => 'C1', 'floor' => 1, 'room_number' => 102, 'area' => 35.0, 'bedrooms' => 1, 'type' => 'studio', 'status' => 'occupied', 'owner_id' => $createdUsers[10]->id],
            ['apartment_number' => 'C1-201', 'block' => 'C1', 'floor' => 2, 'room_number' => 201, 'area' => 95.0, 'bedrooms' => 3, 'type' => '3BR', 'status' => 'occupied', 'owner_id' => $createdUsers[11]->id],
            ['apartment_number' => 'C1-202', 'block' => 'C1', 'floor' => 2, 'room_number' => 202, 'area' => 120.0, 'bedrooms' => 4, 'type' => 'penthouse', 'status' => 'occupied', 'owner_id' => $createdUsers[12]->id],
        ];

        $createdApartments = [];
        foreach ($apartments as $apartmentData) {
            $createdApartments[] = Apartment::create($apartmentData);
        }

        // Tạo Residents (12 residents)
        $residents = [
            ['user_id' => $createdUsers[3]->id, 'apartment_id' => $createdApartments[0]->id, 'relationship' => 'owner', 'move_in_date' => '2023-01-15', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[4]->id, 'apartment_id' => $createdApartments[1]->id, 'relationship' => 'owner', 'move_in_date' => '2023-02-01', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[5]->id, 'apartment_id' => $createdApartments[2]->id, 'relationship' => 'owner', 'move_in_date' => '2023-03-10', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[6]->id, 'apartment_id' => $createdApartments[4]->id, 'relationship' => 'owner', 'move_in_date' => '2023-04-20', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[7]->id, 'apartment_id' => $createdApartments[5]->id, 'relationship' => 'owner', 'move_in_date' => '2023-05-15', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[8]->id, 'apartment_id' => $createdApartments[6]->id, 'relationship' => 'owner', 'move_in_date' => '2023-06-01', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[9]->id, 'apartment_id' => $createdApartments[8]->id, 'relationship' => 'owner', 'move_in_date' => '2023-07-10', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[10]->id, 'apartment_id' => $createdApartments[9]->id, 'relationship' => 'owner', 'move_in_date' => '2023-08-15', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[11]->id, 'apartment_id' => $createdApartments[10]->id, 'relationship' => 'owner', 'move_in_date' => '2023-09-01', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[12]->id, 'apartment_id' => $createdApartments[11]->id, 'relationship' => 'owner', 'move_in_date' => '2023-10-15', 'is_primary_contact' => true],
            ['user_id' => $createdUsers[13]->id, 'apartment_id' => $createdApartments[0]->id, 'relationship' => 'family_member', 'move_in_date' => '2023-01-15', 'is_primary_contact' => false],
            ['user_id' => $createdUsers[14]->id, 'apartment_id' => $createdApartments[1]->id, 'relationship' => 'family_member', 'move_in_date' => '2023-02-01', 'is_primary_contact' => false],
        ];

        foreach ($residents as $residentData) {
            Resident::create($residentData);
        }

        // Tạo Notifications (10 thông báo)
        $notifications = [
            ['title' => 'Thông báo cắt nước định kỳ', 'content' => 'Thông báo cắt nước để bảo trì hệ thống từ 8h-12h ngày 15/08/2025', 'type' => 'maintenance', 'priority' => 'high', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Họp cư dân tháng 8', 'content' => 'Cuộc họp cư dân định kỳ tháng 8 sẽ được tổ chức vào 19h ngày 20/08/2025', 'type' => 'event', 'priority' => 'normal', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Quy định mới về gửi xe', 'content' => 'Áp dụng quy định mới về gửi xe từ ngày 01/09/2025', 'type' => 'general', 'priority' => 'normal', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Bảo trì thang máy Block A', 'content' => 'Thang máy Block A sẽ được bảo trì từ 8h-17h ngày 25/08/2025', 'type' => 'maintenance', 'priority' => 'high', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Thông báo thu phí tháng 8', 'content' => 'Phí quản lý tháng 8 đã được gửi, vui lòng thanh toán trước ngày 30/08/2025', 'type' => 'payment', 'priority' => 'normal', 'created_by' => $createdUsers[1]->id],
            ['title' => 'Lễ trung thu cư dân', 'content' => 'Tổ chức lễ trung thu cho các em nhỏ trong khu chung cư', 'type' => 'event', 'priority' => 'low', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Cập nhật hệ thống camera', 'content' => 'Hệ thống camera an ninh sẽ được nâng cấp từ ngày 01/09/2025', 'type' => 'maintenance', 'priority' => 'normal', 'created_by' => $createdUsers[2]->id],
            ['title' => 'Thông báo về việc xử lý rác thải', 'content' => 'Quy định mới về phân loại và xử lý rác thải trong khu chung cư', 'type' => 'general', 'priority' => 'normal', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Bảo trì hệ thống điện', 'content' => 'Cắt điện để bảo trì hệ thống từ 6h-8h sáng ngày 18/08/2025', 'type' => 'maintenance', 'priority' => 'high', 'created_by' => $createdUsers[2]->id],
            ['title' => 'Khảo sát hài lòng cư dân', 'content' => 'Mời cư dân tham gia khảo sát mức độ hài lòng về dịch vụ quản lý', 'type' => 'general', 'priority' => 'low', 'created_by' => $createdUsers[0]->id],
        ];

        foreach ($notifications as $notificationData) {
            $notification = Notification::create($notificationData);
            
            // Tạo notification recipients cho một số thông báo
            if (in_array($notification->id, [1, 2, 4, 5, 9])) {
                // Gửi cho tất cả cư dân
                for ($i = 3; $i <= 12; $i++) {
                    NotificationRecipient::create([
                        'notification_id' => $notification->id,
                        'user_id' => $createdUsers[$i]->id,
                        'read_at' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
                        'delivery_status' => 'delivered',
                    ]);
                }
            }
        }

        // Tạo Feedbacks (8 feedback)
        $feedbacks = [
            ['title' => 'Đề xuất cải thiện hệ thống thang máy', 'content' => 'Thang máy thường xuyên bị kẹt, cần được bảo trì thường xuyên hơn', 'type' => 'maintenance_request', 'priority' => 'high', 'user_id' => $createdUsers[3]->id, 'apartment_id' => $createdApartments[0]->id],
            ['title' => 'Cảm ơn dịch vụ bảo vệ', 'content' => 'Đội bảo vệ rất nhiệt tình và chu đáo', 'type' => 'general', 'priority' => 'low', 'user_id' => $createdUsers[4]->id, 'apartment_id' => $createdApartments[1]->id],
            ['title' => 'Khiếu nại về tiếng ồn', 'content' => 'Căn hộ bên cạnh thường xuyên gây tiếng ồn vào ban đêm', 'type' => 'complaint', 'priority' => 'normal', 'user_id' => $createdUsers[5]->id, 'apartment_id' => $createdApartments[2]->id],
            ['title' => 'Đề xuất lắp đặt máy lạnh khu vực lobby', 'content' => 'Khu vực sảnh chính quá nóng vào mùa hè', 'type' => 'suggestion', 'priority' => 'normal', 'user_id' => $createdUsers[6]->id, 'apartment_id' => $createdApartments[4]->id],
            ['title' => 'Vấn đề về hệ thống nước', 'content' => 'Áp lực nước yếu vào giờ cao điểm', 'type' => 'maintenance_request', 'priority' => 'normal', 'user_id' => $createdUsers[7]->id, 'apartment_id' => $createdApartments[5]->id],
            ['title' => 'Đề xuất tổ chức thêm hoạt động cộng đồng', 'content' => 'Nên tổ chức thêm các hoạt động để cư dân gắn kết', 'type' => 'suggestion', 'priority' => 'low', 'user_id' => $createdUsers[8]->id, 'apartment_id' => $createdApartments[6]->id],
            ['title' => 'Khiếu nại về dịch vụ vệ sinh', 'content' => 'Khu vực hành lang không được vệ sinh sạch sẽ', 'type' => 'complaint', 'priority' => 'normal', 'user_id' => $createdUsers[9]->id, 'apartment_id' => $createdApartments[8]->id],
            ['title' => 'Cảm ơn ban quản lý', 'content' => 'Cảm ơn ban quản lý đã xử lý nhanh chóng các vấn đề', 'type' => 'general', 'priority' => 'low', 'user_id' => $createdUsers[10]->id, 'apartment_id' => $createdApartments[9]->id],
        ];

        foreach ($feedbacks as $feedbackData) {
            Feedback::create($feedbackData);
        }

        // Tạo Invoices (9 hóa đơn cho các căn hộ có người ở)
        $invoices = [];
        $occupiedApartments = [$createdApartments[0], $createdApartments[1], $createdApartments[2], $createdApartments[4], $createdApartments[5], $createdApartments[6], $createdApartments[8], $createdApartments[9], $createdApartments[10]]; // Skip apartment 3 và 7 (vacant và maintenance)
        
        for ($i = 0; $i < 9; $i++) {
            $invoices[] = [
                'invoice_number' => 'INV-2025-08-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'apartment_id' => $occupiedApartments[$i]->id,
                'billing_period_start' => '2025-08-01',
                'billing_period_end' => '2025-08-31',
                'due_date' => '2025-09-15',
                'management_fee' => 500000,
                'electricity_fee' => rand(200000, 800000),
                'water_fee' => rand(100000, 300000),
                'parking_fee' => 200000,
                'other_fees' => rand(0, 100000),
                'total_amount' => 0, // Sẽ tính sau
                'paid_amount' => 0,
                'status' => ['pending', 'paid', 'partial'][rand(0, 2)],
                'created_by' => $createdUsers[1]->id,
            ];
        }

        // Tính total_amount và tạo invoice
        $createdInvoices = [];
        foreach ($invoices as &$invoice) {
            $invoice['total_amount'] = $invoice['management_fee'] + $invoice['electricity_fee'] + $invoice['water_fee'] + $invoice['parking_fee'] + $invoice['other_fees'];
            if ($invoice['status'] === 'paid') {
                $invoice['paid_amount'] = $invoice['total_amount'];
            } elseif ($invoice['status'] === 'partial') {
                $invoice['paid_amount'] = $invoice['total_amount'] * 0.5;
            }
            $createdInvoices[] = Invoice::create($invoice);
        }

        // Tạo Payments (6 payments)
        $payments = [
            ['payment_number' => 'PAY-2025-001', 'invoice_id' => $createdInvoices[0]->id, 'user_id' => $createdUsers[3]->id, 'amount' => 1200000, 'payment_method' => 'bank_transfer', 'status' => 'completed', 'paid_at' => '2025-08-05 10:30:00', 'processed_by' => $createdUsers[1]->id],
            ['payment_number' => 'PAY-2025-002', 'invoice_id' => $createdInvoices[1]->id, 'user_id' => $createdUsers[4]->id, 'amount' => 1150000, 'payment_method' => 'cash', 'status' => 'completed', 'paid_at' => '2025-08-06 14:15:00', 'processed_by' => $createdUsers[1]->id],
            ['payment_number' => 'PAY-2025-003', 'invoice_id' => $createdInvoices[2]->id, 'user_id' => $createdUsers[5]->id, 'amount' => 600000, 'payment_method' => 'bank_transfer', 'status' => 'completed', 'paid_at' => '2025-08-07 09:45:00', 'processed_by' => $createdUsers[1]->id],
            ['payment_number' => 'PAY-2025-004', 'invoice_id' => $createdInvoices[3]->id, 'user_id' => $createdUsers[6]->id, 'amount' => 1300000, 'payment_method' => 'e_wallet', 'status' => 'completed', 'paid_at' => '2025-08-08 16:20:00', 'processed_by' => $createdUsers[1]->id],
            ['payment_number' => 'PAY-2025-005', 'invoice_id' => $createdInvoices[4]->id, 'user_id' => $createdUsers[7]->id, 'amount' => 1100000, 'payment_method' => 'bank_transfer', 'status' => 'completed', 'paid_at' => '2025-08-09 11:00:00', 'processed_by' => $createdUsers[1]->id],
            ['payment_number' => 'PAY-2025-006', 'invoice_id' => $createdInvoices[5]->id, 'user_id' => $createdUsers[8]->id, 'amount' => 500000, 'payment_method' => 'cash', 'status' => 'pending', 'paid_at' => null, 'processed_by' => $createdUsers[1]->id],
        ];

        foreach ($payments as $paymentData) {
            Payment::create($paymentData);
        }

        // Tạo Devices (12 thiết bị)
        $devices = [
            ['name' => 'Thang máy Block A1', 'device_code' => 'ELV-A1-001', 'category' => 'elevator', 'location' => 'Block A1', 'status' => 'active', 'installation_date' => '2023-01-01'],
            ['name' => 'Thang máy Block B1', 'device_code' => 'ELV-B1-001', 'category' => 'elevator', 'location' => 'Block B1', 'status' => 'maintenance', 'installation_date' => '2023-01-01'],
            ['name' => 'Camera an ninh Lobby', 'device_code' => 'SEC-LB-001', 'category' => 'security', 'location' => 'Lobby chính', 'status' => 'active', 'installation_date' => '2023-02-15'],
            ['name' => 'Camera hành lang tầng 1 Block A', 'device_code' => 'SEC-A1-001', 'category' => 'security', 'location' => 'Tầng 1 Block A1', 'status' => 'active', 'installation_date' => '2023-02-15'],
            ['name' => 'Máy phát điện dự phòng', 'device_code' => 'GEN-BM-001', 'category' => 'generator', 'location' => 'Tầng hầm', 'status' => 'active', 'installation_date' => '2023-01-01'],
            ['name' => 'Hệ thống cứu hỏa Block A', 'device_code' => 'SEC-A1-002', 'category' => 'security', 'location' => 'Block A1', 'status' => 'active', 'installation_date' => '2023-01-01'],
            ['name' => 'Hệ thống cứu hỏa Block B', 'device_code' => 'SEC-B1-001', 'category' => 'security', 'location' => 'Block B1', 'status' => 'active', 'installation_date' => '2023-01-01'],
            ['name' => 'Máy bơm nước chính', 'device_code' => 'WP-BM-001', 'category' => 'water_pump', 'location' => 'Tầng hầm', 'status' => 'active', 'installation_date' => '2023-01-01'],
            ['name' => 'Cửa từ tự động Lobby', 'device_code' => 'SEC-LB-002', 'category' => 'security', 'location' => 'Lobby chính', 'status' => 'active', 'installation_date' => '2023-03-01'],
            ['name' => 'Hệ thống âm thanh Lobby', 'device_code' => 'OTH-LB-001', 'category' => 'other', 'location' => 'Lobby chính', 'status' => 'active', 'installation_date' => '2023-03-15'],
            ['name' => 'Máy lạnh khu vực Lobby', 'device_code' => 'AC-LB-001', 'category' => 'air_conditioner', 'location' => 'Lobby chính', 'status' => 'inactive', 'installation_date' => '2023-04-01'],
            ['name' => 'Camera bãi xe', 'device_code' => 'SEC-BM-001', 'category' => 'security', 'location' => 'Bãi xe tầng hầm', 'status' => 'active', 'installation_date' => '2023-05-01'],
        ];

        $createdDevices = [];
        foreach ($devices as $deviceData) {
            $createdDevices[] = Device::create($deviceData);
        }

        // Tạo Maintenances (10 bảo trì)
        $maintenances = [
            ['device_id' => $createdDevices[0]->id, 'title' => 'Bảo trì định kỳ thang máy Block A1', 'description' => 'Kiểm tra và bảo dưỡng hệ thống thang máy', 'type' => 'preventive', 'priority' => 'normal', 'scheduled_date' => '2025-08-15', 'assigned_technician' => $createdUsers[2]->id],
            ['device_id' => $createdDevices[1]->id, 'title' => 'Sửa chữa thang máy Block B1', 'description' => 'Thang máy bị kẹt, cần sửa chữa khẩn cấp', 'type' => 'corrective', 'priority' => 'high', 'scheduled_date' => '2025-08-12', 'completed_at' => '2025-08-12 16:30:00', 'assigned_technician' => $createdUsers[2]->id, 'status' => 'completed'],
            ['device_id' => $createdDevices[2]->id, 'title' => 'Vệ sinh camera an ninh', 'description' => 'Vệ sinh ống kính camera để đảm bảo chất lượng hình ảnh', 'type' => 'preventive', 'priority' => 'low', 'scheduled_date' => '2025-08-20', 'assigned_technician' => $createdUsers[2]->id],
            ['device_id' => $createdDevices[7]->id, 'title' => 'Bảo trì máy bơm nước', 'description' => 'Kiểm tra và thay dầu máy bơm nước', 'type' => 'preventive', 'priority' => 'normal', 'scheduled_date' => '2025-08-25', 'assigned_technician' => $createdUsers[2]->id],
            ['device_id' => $createdDevices[5]->id, 'title' => 'Kiểm tra hệ thống cứu hỏa', 'description' => 'Kiểm tra định kỳ hệ thống báo cháy và chữa cháy', 'type' => 'preventive', 'priority' => 'high', 'scheduled_date' => '2025-08-30', 'assigned_technician' => $createdUsers[2]->id],
            ['device_id' => $createdDevices[10]->id, 'title' => 'Sửa chữa máy lạnh Lobby', 'description' => 'Máy lạnh không hoạt động, cần kiểm tra và sửa chữa', 'type' => 'corrective', 'priority' => 'normal', 'scheduled_date' => '2025-08-18', 'assigned_technician' => $createdUsers[2]->id, 'status' => 'in_progress'],
            ['device_id' => $createdDevices[8]->id, 'title' => 'Bảo trì cửa từ tự động', 'description' => 'Cửa từ đóng mở không ổn định', 'type' => 'corrective', 'priority' => 'normal', 'scheduled_date' => '2025-08-22', 'assigned_technician' => $createdUsers[2]->id],
            ['device_id' => $createdDevices[11]->id, 'title' => 'Vệ sinh camera bãi xe', 'description' => 'Camera bãi xe bị mờ, cần vệ sinh', 'type' => 'preventive', 'priority' => 'low', 'scheduled_date' => '2025-08-28', 'assigned_technician' => $createdUsers[2]->id],
            ['device_id' => $createdDevices[4]->id, 'title' => 'Kiểm tra máy phát điện', 'description' => 'Kiểm tra hoạt động máy phát điện dự phòng', 'type' => 'preventive', 'priority' => 'high', 'scheduled_date' => '2025-09-01', 'assigned_technician' => $createdUsers[2]->id],
            ['device_id' => $createdDevices[9]->id, 'title' => 'Bảo trì hệ thống âm thanh', 'description' => 'Kiểm tra và điều chỉnh hệ thống âm thanh Lobby', 'type' => 'preventive', 'priority' => 'low', 'scheduled_date' => '2025-09-05', 'assigned_technician' => $createdUsers[2]->id],
        ];

        foreach ($maintenances as $maintenanceData) {
            Maintenance::create($maintenanceData);
        }

        // Tạo Events (8 sự kiện)
        $events = [
            ['title' => 'Họp cư dân tháng 8', 'description' => 'Họp định kỳ hàng tháng để thảo luận các vấn đề chung', 'type' => 'meeting', 'start_time' => '2025-08-20 19:00:00', 'end_time' => '2025-08-20 21:00:00', 'location' => 'Sảnh chính', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Lễ trung thu cho thiếu nhi', 'description' => 'Tổ chức lễ hội trung thu cho các em nhỏ trong khu chung cư', 'type' => 'social_event', 'start_time' => '2025-09-15 18:00:00', 'end_time' => '2025-09-15 21:00:00', 'location' => 'Khu vui chơi', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Khóa học yoga buổi sáng', 'description' => 'Lớp yoga miễn phí cho cư dân', 'type' => 'social_event', 'start_time' => '2025-08-25 06:30:00', 'end_time' => '2025-08-25 07:30:00', 'location' => 'Sảnh chính', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Bảo trì hệ thống điện toàn tòa', 'description' => 'Cắt điện để bảo trì hệ thống điện', 'type' => 'power_outage', 'start_time' => '2025-08-18 06:00:00', 'end_time' => '2025-08-18 08:00:00', 'location' => 'Toàn bộ tòa nhà', 'created_by' => $createdUsers[2]->id],
            ['title' => 'Ngày hội thể thao cư dân', 'description' => 'Tổ chức các trò chơi thể thao cho cư dân', 'type' => 'social_event', 'start_time' => '2025-09-01 08:00:00', 'end_time' => '2025-09-01 12:00:00', 'location' => 'Sân thể thao', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Họp khẩn về an ninh', 'description' => 'Họp khẩn cấp về vấn đề an ninh trong khu vực', 'type' => 'emergency', 'start_time' => '2025-08-16 20:00:00', 'end_time' => '2025-08-16 21:30:00', 'location' => 'Phòng họp', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Tập huấn PCCC', 'description' => 'Khóa tập huấn phòng cháy chữa cháy cho cư dân', 'type' => 'meeting', 'start_time' => '2025-08-30 14:00:00', 'end_time' => '2025-08-30 17:00:00', 'location' => 'Sảnh chính', 'created_by' => $createdUsers[0]->id],
            ['title' => 'Chợ phiên cuối tuần', 'description' => 'Tổ chức chợ phiên bán các sản phẩm tự làm của cư dân', 'type' => 'social_event', 'start_time' => '2025-09-07 07:00:00', 'end_time' => '2025-09-07 11:00:00', 'location' => 'Khu vườn', 'created_by' => $createdUsers[0]->id],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        // Tạo Votes (5 votes)
        $votes = [
            [
                'title' => 'Biểu quyết nâng cấp hệ thống thang máy',
                'description' => 'Đề xuất nâng cấp toàn bộ hệ thống thang máy để đảm bảo an toàn',
                'type' => 'facility_upgrade',
                'scope' => 'all',
                'start_time' => '2025-08-15 08:00:00',
                'end_time' => '2025-08-25 20:00:00',
                'status' => 'active',
                'created_by' => $createdUsers[0]->id,
            ],
            [
                'title' => 'Phê duyệt ngân sách năm 2026',
                'description' => 'Biểu quyết phê duyệt dự toán ngân sách cho năm 2026',
                'type' => 'budget_approval',
                'scope' => 'all',
                'start_time' => '2025-08-20 08:00:00',
                'end_time' => '2025-08-30 20:00:00',
                'status' => 'active',
                'created_by' => $createdUsers[0]->id,
            ],
            [
                'title' => 'Thay đổi quy định gửi xe',
                'description' => 'Đề xuất thay đổi quy định và phí gửi xe mới',
                'type' => 'rule_change',
                'scope' => 'all',
                'start_time' => '2025-08-10 08:00:00',
                'end_time' => '2025-08-20 20:00:00',
                'status' => 'closed',
                'created_by' => $createdUsers[0]->id,
            ],
            [
                'title' => 'Lắp đặt camera bổ sung Block C',
                'description' => 'Biểu quyết việc lắp đặt thêm camera an ninh tại Block C',
                'type' => 'facility_upgrade',
                'scope' => 'block',
                'target_scope' => json_encode(['C1']),
                'start_time' => '2025-08-12 08:00:00',
                'end_time' => '2025-08-22 20:00:00',
                'status' => 'active',
                'created_by' => $createdUsers[0]->id,
            ],
            [
                'title' => 'Tổ chức lễ hội cuối năm',
                'description' => 'Biểu quyết tổ chức lễ hội cuối năm cho cư dân',
                'type' => 'other',
                'scope' => 'all',
                'start_time' => '2025-08-01 08:00:00',
                'end_time' => '2025-08-10 20:00:00',
                'status' => 'closed',
                'created_by' => $createdUsers[0]->id,
            ],
        ];

        foreach ($votes as $index => $voteData) {
            $vote = Vote::create($voteData);
            
            // Tạo vote options cho mỗi vote
            $options = [
                0 => ['Đồng ý nâng cấp', 'Không đồng ý', 'Trì hoãn đến năm sau'],
                1 => ['Phê duyệt ngân sách', 'Yêu cầu điều chỉnh', 'Từ chối ngân sách'],
                2 => ['Đồng ý thay đổi', 'Giữ quy định cũ', 'Cần thảo luận thêm'],
                3 => ['Đồng ý lắp đặt', 'Không cần thiết', 'Lắp đặt ở vị trí khác'],
                4 => ['Tổ chức lễ hội', 'Không tổ chức', 'Tổ chức đơn giản'],
                5 => ['Đồng ý lắp đặt CCTV', 'Không cần thiết', 'Cân nhắc thêm'],
            ];
            
            foreach ($options[$index] as $optionIndex => $optionText) {
                VoteOption::create([
                    'vote_id' => $vote->id,
                    'option_text' => $optionText,
                    'sort_order' => $optionIndex + 1,
                ]);
            }
            
            // Tạo vote responses cho một số votes
            if (in_array($index, [2, 4])) { // Votes đã đóng (index 2,4)
                $voteOptions = VoteOption::where('vote_id', $vote->id)->get();
                for ($i = 3; $i <= 12; $i++) {
                    if (rand(0, 1)) { // 50% chance user voted
                        VoteResponse::create([
                            'vote_id' => $vote->id,
                            'user_id' => $createdUsers[$i]->id,
                            'vote_option_id' => $voteOptions->random()->id,
                            'voted_at' => now()->subDays(rand(1, 15)),
                            'comment' => rand(0, 1) ? 'Tôi đồng ý với đề xuất này.' : null,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Đã tạo dữ liệu mẫu thành công!');
        $this->command->info('- 15 Users (1 admin, 1 accountant, 1 technician, 12 residents)');
        $this->command->info('- 12 Apartments');
        $this->command->info('- 12 Residents');
        $this->command->info('- 10 Notifications');
        $this->command->info('- 8 Feedbacks');
        $this->command->info('- 9 Invoices');
        $this->command->info('- 6 Payments');
        $this->command->info('- 12 Devices');
        $this->command->info('- 10 Maintenances');
        $this->command->info('- 8 Events');
        $this->command->info('- 5 Votes với options và responses');
    }
}
