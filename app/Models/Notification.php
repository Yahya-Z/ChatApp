<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // Fillable fields which are used to create a new notification
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    // Casts which are used to convert the data to an array
    protected $casts = [
        'data' => 'array',
        'read_At' => 'datetime',
    ];

    // User relationship which is used to get the user who created the notification
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Notifiable relationship which is used to get the notifiable model
    public function notifiable()
    {
        return $this->morphTo();
    }
}
