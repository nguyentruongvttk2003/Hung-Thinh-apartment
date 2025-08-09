<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $recipients;

    /**
     * Create a new event instance.
     */
    public function __construct($notification, $recipients = [])
    {
        $this->notification = $notification;
        $this->recipients = $recipients;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        $channels = [];
        
        // Broadcast to all users if no specific recipients
        if (empty($this->recipients)) {
            $channels[] = new Channel('notifications.all');
        } else {
            // Broadcast to specific users
            foreach ($this->recipients as $userId) {
                $channels[] = new PrivateChannel("notifications.user.{$userId}");
            }
        }
        
        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'priority' => $this->notification->priority,
            'scope' => $this->notification->scope,
            'created_at' => $this->notification->created_at->toISOString(),
            'actions' => $this->notification->actions ?? [],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'notification.sent';
    }
}
