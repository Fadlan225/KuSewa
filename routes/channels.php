<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    $room = \App\Models\room_chat::with('ownerProfile')->find($roomId);
    if (!$room) return false;
    
    return $room->user_id === $user->id || ($room->ownerProfile && $room->ownerProfile->user_id === $user->id);
});
