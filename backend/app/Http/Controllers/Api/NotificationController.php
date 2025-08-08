<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orWhere('user_id', null) // Global notifications
            ->latest()
            ->paginate(15);

        return response()->json($notifications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string',
        ]);

        $notification = Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'user_id' => $request->user_id,
            'status' => 'active',
        ]);

        return response()->json($notification, 201);
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

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        $user = auth()->user();
        $count = Notification::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('user_id', null);
        })
        ->whereDoesntHave('recipients', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->count();

        return response()->json(['count' => $count]);
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
