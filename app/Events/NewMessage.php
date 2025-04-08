<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $message;
    public $sender;
    public $created_at;
    public $receiver_id;

    public function __construct($message)
    {
        $this->id = $message->id;
        $this->message = decrypt($message->encrypted_message);
        $this->sender = $message->sender;
        $this->created_at = $message->created_at;
        $this->receiver_id = $message->receiver_id;
        
        // Log the message details
        Log::info('NewMessage Event - Constructor', [
            'message_id' => $this->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver_id,
            'message' => $this->message
        ]);
    }

    /**
     * احصل على القنوات التي يجب أن يبث فيها الحدث.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // Create a unique channel name for this conversation
        $channelName = 'chat.' . min($this->sender->id, $this->receiver_id) . '.' . max($this->sender->id, $this->receiver_id);
        
        Log::info('NewMessage Event - Broadcasting on public conversation channel', [
            'channel' => $channelName,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver_id,
            'message_id' => $this->id,
            'sender_name' => $this->sender->name,
            'message_content' => $this->message
        ]);
        
        return new Channel($channelName);
    }

    /**
     * The data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith()
    {
        $data = [
            'id' => $this->id,
            'message' => $this->message,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name
            ],
            'receiver_id' => $this->receiver_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'status' => 'sent'
        ];
        
        Log::info('NewMessage Event - Broadcasting data', [
            'data' => $data,
            'channel' => 'chat.' . min($this->sender->id, $this->receiver_id) . '.' . max($this->sender->id, $this->receiver_id)
        ]);
        
        return $data;
    }
    
    /**
     * الحصول على اسم الحدث المبثوث.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'NewMessage';
    }
}
