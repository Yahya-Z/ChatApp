<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Broadcast;

// Home route
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard route
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

// Check authentication route
Route::get('/auth/check', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->check() ? auth()->user() : null
    ]);
});

// Middleware group
Route::middleware(['auth'])->group(function () {
    // Users routes
    Route::get('/users', [ChatController::class, 'index'])->name('users.index');
    
    // Messages routes
    Route::get('/messages/{user}', [ChatController::class, 'getMessages']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    
    // Get current user route
    Route::get('/user', function () {
        return response()->json(auth()->user());
    });
});

// Register broadcast routes
Broadcast::routes(['middleware' => ['auth']]);

// Include settings and auth routes
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('/check-pusher-config', function () {
    return [
        'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
        'PUSHER_APP_SECRET' => env('PUSHER_APP_SECRET'),
        'PUSHER_APP_ID' => env('PUSHER_APP_ID'),
        'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
        'BROADCAST_DRIVER' => env('BROADCAST_DRIVER'),
        'BROADCAST_CONNECTION' => env('BROADCAST_CONNECTION'),
    ];
});

