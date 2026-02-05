<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Chat extends Model
{
    use HasFactory;
    protected $appends = ['unread_count', 'display_date'];

    protected $fillable = [
        'room_id',
        'type',
        'sender_id',
        'receiver_id', 
        'message', 
        'read_flg',
    ];

    // 送信者
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // 受信者
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    
    // チャットルーム
    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    // 既読カウントアクセサ
    public function getUnreadCountAttribute()
    {
        return Chat::where('sender_id', $this->sender_id)
            ->where('receiver_id', auth()->id())
            ->where('read_flg', 0)
            ->count();
    }
    // 既読カウントアクセサ
    public function getUnreadCountForYouAttribute()
    {
        return Chat::where('sender_id', $this->receiver_id)
            ->where('receiver_id', auth()->id())
            ->where('read_flg', 0)
            ->count();
    }
    // 日付表示アクセサ
    public function getDisplayDateAttribute()
    {
        if (!$this->created_at) {
            return '';
        }

        $dt = Carbon::parse($this->created_at);

        if ($dt->isToday()) {
            // 今日のメッセージ → 時刻のみ
            return $dt->format('G:i');
        } elseif ($dt->isCurrentYear()) {
            // 今年のメッセージ → 月日
            return $dt->format('n/j');
        } else {
            // 去年以前 → 年+月日
            return $dt->format('Y/n/j');
        }
    }

    // チャットログの日付表示アクセサ
    public function getDisplayChatLogDateAttribute()
    {
        if (!$this->created_at) {
            return '';
        }

        $dt = Carbon::parse($this->created_at);

        if ($dt->isCurrentYear()) {
            // 今年のメッセージ → 月日
            return $dt->format('n月j日');
        } else {
            // 去年以前 → 年+月日
            return $dt->format('Y年n月j日');
        }
    }
    //urlにハイパーリンクを設定
    public function getMessageHtmlAttribute()
    {
        $text = e($this->message); // XSS対策

        $pattern = '/(https?:\/\/[^\s]+)/i';
        $replace = '<a href="$1" target="_blank" class="underline ">$1</a>';

        return nl2br(preg_replace($pattern, $replace, $text));
    }
}