<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// //ユーザー間のチャンネル
// Broadcast::channel('chat.{user1}.{user2}', function ($user, $user1, $user2) {
//     return in_array($user->id, [$user1, $user2]);
// });

//ユーザー個別のチャンネル
Broadcast::channel('user.{user_id}', function ($user, $user_id) {
    return (int) $user->id === (int) $user_id;
});

//ユーザー間のチャンネル
Broadcast::channel('chat.room.{room_id}', function ($user, $room_id) {
    return ChatRoom::where('id', $room_id)
        ->whereHas('members', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->exists();
});