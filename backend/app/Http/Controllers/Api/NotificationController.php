<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        \Log::info('NotificationController index called', [
            'params' => $request->all(),
            'user' => auth()->user()?->id
        ]);

        try {
            $query = Notification::query();
            
            \Log::info('Initial notification count', ['count' => $query->count()]);
            
            // Filter by type if provided - only if not empty
            if ($request->has('type') && !empty($request->type)) {
                $query->where('type', $request->type);
                \Log::info('Filtered by type', ['type' => $request->type, 'count' => $query->count()]);
            }

            // Filter by status if provided - only if not empty
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
                \Log::info('Filtered by status', ['status' => $request->status, 'count' => $query->count()]);
            }

            // Search by title if provided - only if not empty
            if ($request->has('search') && !empty($request->search)) {
                $query->where('title', 'like', '%' . $request->search . '%');
                \Log::info('Filtered by search', ['search' => $request->search, 'count' => $query->count()]);
            }

            \Log::info('Final query count before pagination', ['count' => $query->count()]);

            $notifications = $query->latest()->paginate($request->get('per_page', 15));

            \Log::info('Paginated result', [
                'items_count' => $notifications->count(),
                'total' => $notifications->total(),
                'current_page' => $notifications->currentPage()
            ]);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'message' => 'Notifications retrieved successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load notifications', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('NotificationController store called', [
            'data' => $request->all(),
            'user' => auth()->user()?->id
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,maintenance,payment,event,emergency',
            'priority' => 'required|in:low,medium,high',
        ]);

        if ($validator->fails()) {
            \Log::error('Notification validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Map frontend priority values to database values
        $priorityMap = [
            'low' => 'low',
            'medium' => 'normal', 
            'high' => 'high'
        ];
        $priority = $priorityMap[$request->priority] ?? 'normal';

        \Log::info('Creating notification with data', $validator->validated());

        try {
            $notification = Notification::create([
                'title' => $request->title,
                'content' => $request->content,
                'type' => $request->type,
                'priority' => $priority,
                'created_by' => auth()->user()->id,
                'status' => 'draft',
            ]);

            \Log::info('Notification created successfully', ['id' => $notification->id]);

            return response()->json([
                'success' => true,
                'data' => $notification,
                'message' => 'Thông báo đã được tạo thành công'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to create notification', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        return response()->json($notification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        
        $request->validate([
            'title' => 'string|max:255',
            'message' => 'string',
            'type' => 'string',
            'status' => 'string',
        ]);

        $notification->update($request->all());

        return response()->json($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        try {
            $user = auth()->user();
            $notification = Notification::findOrFail($id);
            
            // Create or update notification recipient record
            $recipient = $notification->recipients()->where('user_id', $user->id)->first();
            if ($recipient) {
                $recipient->update(['read_at' => now()]);
            } else {
                $notification->recipients()->create([
                    'user_id' => $user->id,
                    'read_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đánh dấu thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            $user = auth()->user();
            
            $notifications = Notification::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('user_id', null);
            })
            ->whereDoesntHave('recipients', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

            foreach ($notifications as $notification) {
                $notification->recipients()->firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'read_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đánh dấu tất cả thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        try {
            $user = auth()->user();
            $count = Notification::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('user_id', null);
            })
            ->whereDoesntHave('recipients', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

            return response()->json([
                'success' => true,
                'data' => ['count' => $count],
                'message' => 'Unread count retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy số lượng thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification.
     */
    public function send(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        // Logic to send notification would go here
        return response()->json(['message' => 'Notification sent successfully']);
    }

    /**
     * Get received notifications.
     */
    public function received()
    {
        $user = auth()->user();
        $notifications = $user->receivedNotifications()->with('notification')->latest()->get();
        return response()->json($notifications);
    }
}
