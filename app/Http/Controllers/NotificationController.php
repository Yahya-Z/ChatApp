<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return auth()->user()->notifications()->latest()->get();
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['read_At' => now()]);
        return response()->json(['success' => true]);
    }
}
