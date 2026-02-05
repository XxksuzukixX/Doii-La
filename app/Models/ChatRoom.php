<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'icon_path',
        'created_by',
        'last_message_at',
    ];

    /**
     * 作成者
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 参加ユーザー（中間）
     */
    // public function members()
    // {
    //     return $this->hasMany(ChatRoomUser::class, 'room_id');
    // }

    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'chat_room_users',
            'room_id',
            'user_id'
        );
    }
    public function activeMembers()
    {
        return $this->belongsToMany(
            User::class,
            'chat_room_users',
            'room_id',
            'user_id'
        )->whereNull('chat_room_users.left_at');
    }
    
    public function room_users()
    {
        return $this->hasMany(ChatRoomUser::class, 'room_id');
    }

    /**
     * 参加ユーザー（User として）
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_room_users', 'room_id', 'user_id')
            ->withPivot([
                'role',
                'joined_at',
                'left_at',
                'last_read_chat_id',
                'last_read_at',
            ]);
    }

    /**
     * メッセージ
     */
    public function chats()
    {
        return $this->hasMany(Chat::class, 'room_id');
    }

    /**
     * 最新メッセージ
     */
    public function latestChat()
    {
        return $this->hasOne(Chat::class, 'room_id')->latestOfMany();
    }
    // チャットルーム表示名取得
    public function getDisplayNameAttribute(): string
    {
        // グループチャット
        if ($this->type === 'group') {
            return (string) $this->name;
        }

        // private（1対1）
        if ($this->type === 'private') {
            $myUserId = auth()->id();

            $partner = $this->users
                ->firstWhere('id', '!=', $myUserId);

            return $partner?->name ?? '不明なユーザー';
        }

        return '';
    }
}
