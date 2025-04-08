<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     */
    // Constructor which is used to create a new notification
    public function __construct($notification)
    {
        $this ->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    // Broadcast on which is used to broadcast the notification
    public function broadcastOn()
    {
        return new PrivateChannel('notifications.' . $this->notification->user_id);
    }

    // this is used to broadcast the notification as a new notification
    public function broadcastAs()
    {
        return 'NewNotification';
    }

    // the diffrences between broadcastOn and broadcastWith is that broadcastOn is used to broadcast the notification to the user and broadcastWith is used to broadcast the notification to the user with the notification data
    public function broadcastWith()
    {
        return ['notification' => $this->notification];
    }

    // this is used to check if the notification is not the same as the current user
    public function broadcastWhen()
    {
        return $this->notification->user_id !== auth()->id();
    }
}
