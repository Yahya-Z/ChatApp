<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ChatController;
use App\Models\User;
use App\Http\Controllers\NotificationController;


// Use the routes in web.php instead of api.php for handling users
Route::middleware(['web', 'auth'])->group(function () {
    // Get all users
    Route::get('/users', [ChatController::class, 'users']);

    // Send a message
    Route::post('/messages', [ChatController::class, 'sendMessage']);

    // Get messages
    Route::get('/messages/{user}', [ChatController::class, 'getMessages']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
});
