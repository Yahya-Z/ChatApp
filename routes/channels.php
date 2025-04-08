<?php

use Illuminate\Support\Facades\Broadcast;

// Channel for user authentication
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel for private chat conversations
Broadcast::channel('private-chat.{user1}.{user2}', function ($user, $user1, $user2) {
    // Allow access if the user is either user1 or user2
    return (int) $user->id === (int) $user1 || (int) $user->id === (int) $user2;
});

// Channel for private notifications
Broadcast::channel('private-notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
