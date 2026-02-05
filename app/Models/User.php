<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailCustom;
use App\Notifications\ResetPasswordCustom;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // メール認証通知
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailCustom);
    }

    // パスワード変更通知
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordCustom($token));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'theme',
        'icon_path', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // 予約枠
    public function reservationSlots()
    {
        return $this->belongsToMany(
            ReservationSlot::class,
            'reservation_users',
            'user_id',
            'slot_id'
        )->withPivot('status')->withTimestamps();
    }
    
    // 募集の既読処理
    public function markReservationAsRead(int $reservationId): void
    {
        $this->reservationReads()->syncWithoutDetaching([
            $reservationId => [
                'read_at' => now(),
            ],
        ]);
    }

    // 既読の募集
    public function reservationReads()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_reads')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }
    // チャットルーム
    public function chatRooms()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_users', 'user_id', 'room_id')
            ->withPivot([
                'role',
                'joined_at',
                'left_at',
                'last_read_chat_id',
                'last_read_at',
            ]);
    }
    // 送信メッセージ
    public function sentChats()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }
    
    public function isAdmin(): bool
    {
        return (bool) $this->admin_flg;
    }
}
