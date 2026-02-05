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


class MessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Chat $chat
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(
            'chat.room.' . $this->chat->room_id
        );
    }

    public function broadcastWith(): array
    {
        return [
            'chat' => $this->chat->toArray(),
        ];
    }
}