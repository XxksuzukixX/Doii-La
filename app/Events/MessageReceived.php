<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Chat $chat ブロードキャストするチャット
     * @param int $receiver_id 受信者ユーザーID
     */
    public function __construct(
        public Chat $chat,
        public int $receiver_id
    ) {}

    /**
     * ブロードキャスト先（個別ユーザーのチャンネル）
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->receiver_id);
    }

    /**
     * ブロードキャストするデータ
     */
    public function broadcastWith(): array
    {
        return [
            'chat' => $this->chat->toArray(),
        ];
    }
}