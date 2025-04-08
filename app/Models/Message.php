<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable =[
        'sender_id',
        'receiver_id',
        'group_id',
        'encrypted_message',
        'status'
    ];

    // Relationships

    // A message belongs to a sender (user)  
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    // A message belongs to a receiver (user)  
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    
    // A message belongs to a group
    public function group() {
        return $this->belongsTo(Group::class);
    }
}
