<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\Events\NewMessage;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
// For listing users (main chat page)
// app/Http/Controllers/ChatController.php
public function index()
{
    // Get all users except the current user
    $users = User::where('id', '!=', Auth::id())
        ->withCount(['receivedMessages as unread' => function($query) {
            $query->where('status', 'sent');
        }])
        ->when(request('search'), function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
        ->get();

    // Get the last 50 messages for the current user
    return inertia('Chat/Index', [
        'users' => $users,
        'messages' => Auth::user()->receivedMessages()
            ->with('sender')
            ->latest()
            ->take(50)
            ->get()
    ]);
}

// Get all users
public function users()
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return response()->json([
        'users' => User::where('id', '!=', Auth::id())->get()
    ]);
}

// Get messages
public function getMessages($userId)
{
    // Get the current user id
    $authUserId = Auth::id();

    // Get the messages between the current user and the selected user
    $messages = Message::where(function ($query) use ($authUserId, $userId) {
        $query->where('sender_id', $authUserId)
              ->where('receiver_id', $userId);
    })->orWhere(function ($query) use ($authUserId, $userId) {
        $query->where('sender_id', $userId)
              ->where('receiver_id', $authUserId);
    })
    ->with('sender')
    ->orderBy('created_at', 'asc')
    ->get()
    ->map(function ($message) {
        return [
            'id' => $message->id,
            'message' => decrypt($message->encrypted_message),
            'sender' => $message->sender,
            'created_at' => $message->created_at->toDateTimeString(),
            'status' => $message->status
        ];
    });

    return response()->json($messages);
}

// Send a message
public function sendMessage(Request $request)  
{
    // Validate the request
    $request->validate([
        'message' => 'required|string',
        'receiver_id' => 'required|exists:users,id'
    ]);

    // Create a new message
    $message = Message::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $request->receiver_id,
        'encrypted_message' => encrypt($request->message),
        'status' => 'sent',
    ]);

    // Load the sender
    $message->load('sender');
    
    // Log the message details before broadcasting
    Log::info('Broadcasting new message - Message details:', [
        'message_id' => $message->id,
        'sender_id' => $message->sender_id, 
        'receiver_id' => $message->receiver_id,
        'sender_name' => $message->sender->name,
        'status' => $message->status
    ]);

    // Broadcast the message to both users
    broadcast(new NewMessage($message))->toOthers();
    Log::info('NewMessage event dispatched successfully.');

    // Update message status to delivered
    $message->update(['status' => 'delivered']);

    // Return the message details
    return response()->json([
        'status' => 'success',
        'message' => [
            'id' => $message->id,
            'message' => decrypt($message->encrypted_message),
            'sender' => $message->sender,
            'created_at' => $message->created_at->toDateTimeString(),
            'status' => $message->status
        ]
    ]);
}
}