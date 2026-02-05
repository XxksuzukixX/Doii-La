<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoomUser extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'user_id',
        'role',
        'joined_at',
        'left_at',
        'last_read_chat_id',
        'last_read_at',
        'last_notified_at',
        'last_notified_chat_id',
    ];

    /**
     * ルーム
     */
    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    /**
     * ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 最後に既読したメッセージ
     */
    public function lastReadChat()
    {
        return $this->belongsTo(Chat::class, 'last_read_chat_id');
    }
    /**
     * 最新の未読メッセージ
     */
    public function latestUnreadChat()
    {
        return $this->hasOne(Chat::class, 'room_id', 'room_id')
            ->where(function ($q) {
                $q->whereColumn('chats.id', '>', 'chat_room_users.last_read_chat_id')
                  ->orWhereNull('chat_room_users.last_read_chat_id');
            })
            ->where('chats.type', 'message')
            ->latest('chats.id');
    }
    public function latestChat()
    {
        return $this->hasOne(Chat::class, 'room_id', 'room_id')
            ->where('chats.type', 'message')
            ->latest('chats.id');
    }
}
