<?php

use Illuminate\Support\Facades\Broadcast;

// Private channel for board updates
Broadcast::channel('board.{boardId}', function ($user, $boardId) {
    return $user->canAccessBoard($boardId);
});

// Presence channel for who's viewing a board
Broadcast::channel('board.{boardId}.presence', function ($user, $boardId) {
    if ($user->canAccessBoard($boardId)) {
        return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar_url];
    }
});

// Private channel for user notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
