<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Models\Apartment;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send notification to specific users
     */
    public function sendToUsers(array $userIds, array $notificationData)
    {
        try {
            DB::beginTransaction();

            // Create notification
            $notification = Notification::create([
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'type' => $notificationData['type'] ?? 'general',
                'priority' => $notificationData['priority'] ?? 'normal',
                'scope' => 'specific',
                'scope_value' => implode(',', $userIds),
                'scheduled_at' => $notificationData['scheduled_at'] ?? now(),
                'expires_at' => $notificationData['expires_at'] ?? null,
                'actions' => $notificationData['actions'] ?? null,
                'metadata' => $notificationData['metadata'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create recipients
            $recipients = [];
            foreach ($userIds as $userId) {
                $recipients[] = [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            NotificationRecipient::insert($recipients);

            // Broadcast real-time notification
            broadcast(new NotificationSent($notification, $userIds));

            DB::commit();

            return [
                'success' => true,
                'notification' => $notification,
                'recipients_count' => count($userIds)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to all users
     */
    public function sendToAll(array $notificationData)
    {
        $userIds = User::where('status', 'active')->pluck('id')->toArray();
        
        $notificationData['scope'] = 'all';
        $notificationData['scope_value'] = 'all';
        
        return $this->sendToUsers($userIds, $notificationData);
    }

    /**
     * Send notification to users by role
     */
    public function sendToRole(string $role, array $notificationData)
    {
        $userIds = User::where('role', $role)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
        
        $notificationData['scope'] = 'role';
        $notificationData['scope_value'] = $role;
        
        return $this->sendToUsers($userIds, $notificationData);
    }

    /**
     * Send notification to users in specific block
     */
    public function sendToBlock(string $block, array $notificationData)
    {
        $userIds = User::whereHas('apartment', function($query) use ($block) {
            $query->where('block', $block);
        })
        ->where('status', 'active')
        ->pluck('id')
        ->toArray();
        
        $notificationData['scope'] = 'block';
        $notificationData['scope_value'] = $block;
        
        return $this->sendToUsers($userIds, $notificationData);
    }

    /**
     * Send notification to users on specific floor
     */
    public function sendToFloor(string $block, int $floor, array $notificationData)
    {
        $userIds = User::whereHas('apartment', function($query) use ($block, $floor) {
            $query->where('block', $block)->where('floor', $floor);
        })
        ->where('status', 'active')
        ->pluck('id')
        ->toArray();
        
        $notificationData['scope'] = 'floor';
        $notificationData['scope_value'] = $block . '-' . $floor;
        
        return $this->sendToUsers($userIds, $notificationData);
    }

    /**
     * Send notification to specific apartment
     */
    public function sendToApartment(int $apartmentId, array $notificationData)
    {
        $userIds = User::whereHas('apartment', function($query) use ($apartmentId) {
            $query->where('id', $apartmentId);
        })
        ->where('status', 'active')
        ->pluck('id')
        ->toArray();
        
        $notificationData['scope'] = 'apartment';
        $notificationData['scope_value'] = $apartmentId;
        
        return $this->sendToUsers($userIds, $notificationData);
    }

    /**
     * Schedule notification for later
     */
    public function scheduleNotification(array $notificationData, Carbon $scheduledAt)
    {
        $notificationData['scheduled_at'] = $scheduledAt;
        $notificationData['status'] = 'scheduled';
        
        // For now, we'll store it and process later with a job/cron
        return Notification::create($notificationData);
    }

    /**
     * Mark notification as read for a user
     */
    public function markAsRead(int $notificationId, int $userId)
    {
        return NotificationRecipient::where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId)
    {
        return NotificationRecipient::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(int $userId, int $limit = 10)
    {
        return Notification::whereHas('recipients', function($query) use ($userId) {
            $query->where('user_id', $userId)->whereNull('read_at');
        })
        ->with(['recipients' => function($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Get all notifications for a user (paginated)
     */
    public function getUserNotifications(int $userId, int $page = 1, int $perPage = 20)
    {
        return Notification::whereHas('recipients', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['recipients' => function($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Delete expired notifications
     */
    public function cleanupExpiredNotifications()
    {
        $expiredCount = Notification::where('expires_at', '<', now())
            ->where('expires_at', '!=', null)
            ->delete();
            
        return $expiredCount;
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(int $days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total_sent' => Notification::where('created_at', '>=', $startDate)->count(),
            'total_read' => NotificationRecipient::where('read_at', '>=', $startDate)
                ->whereNotNull('read_at')->count(),
            'total_unread' => NotificationRecipient::whereNull('read_at')
                ->whereHas('notification', function($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                })->count(),
            'by_type' => Notification::where('created_at', '>=', $startDate)
                ->groupBy('type')
                ->selectRaw('type, count(*) as count')
                ->pluck('count', 'type'),
            'by_priority' => Notification::where('created_at', '>=', $startDate)
                ->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority'),
        ];
    }

    /**
     * Send emergency notification
     */
    public function sendEmergencyNotification(array $notificationData)
    {
        $notificationData['type'] = 'emergency';
        $notificationData['priority'] = 'urgent';
        
        return $this->sendToAll($notificationData);
    }

    /**
     * Send maintenance notification
     */
    public function sendMaintenanceNotification(int $maintenanceId, array $affectedApartments = [])
    {
        $maintenance = \App\Models\Maintenance::find($maintenanceId);
        if (!$maintenance) {
            return ['success' => false, 'error' => 'Maintenance not found'];
        }

        $notificationData = [
            'title' => 'Bảo trì thiết bị',
            'message' => "Bảo trì {$maintenance->device->name} được lên lịch vào {$maintenance->scheduled_date}",
            'type' => 'maintenance',
            'priority' => 'high',
            'metadata' => json_encode(['maintenance_id' => $maintenanceId]),
        ];

        if (empty($affectedApartments)) {
            return $this->sendToAll($notificationData);
        } else {
            $userIds = User::whereHas('apartment', function($query) use ($affectedApartments) {
                $query->whereIn('id', $affectedApartments);
            })->pluck('id')->toArray();
            
            return $this->sendToUsers($userIds, $notificationData);
        }
    }

    /**
     * Send payment reminder
     */
    public function sendPaymentReminder(int $invoiceId)
    {
        $invoice = \App\Models\Invoice::find($invoiceId);
        if (!$invoice) {
            return ['success' => false, 'error' => 'Invoice not found'];
        }

        $notificationData = [
            'title' => 'Nhắc nhở thanh toán',
            'message' => "Hóa đơn #{$invoice->invoice_number} với số tiền {$invoice->amount} đến hạn thanh toán",
            'type' => 'payment',
            'priority' => 'high',
            'metadata' => json_encode(['invoice_id' => $invoiceId]),
            'actions' => json_encode([
                ['label' => 'Xem hóa đơn', 'action' => 'view_invoice', 'data' => $invoiceId],
                ['label' => 'Thanh toán ngay', 'action' => 'pay_now', 'data' => $invoiceId]
            ]),
        ];

        $userIds = User::whereHas('apartment', function($query) use ($invoice) {
            $query->where('id', $invoice->apartment_id);
        })->pluck('id')->toArray();

        return $this->sendToUsers($userIds, $notificationData);
    }
}
