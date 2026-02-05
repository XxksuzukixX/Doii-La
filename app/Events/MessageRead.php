<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $room_id,
        public array $chat_ids
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.room.' . $this->room_id);
    }

    public function broadcastWith(): array
    {
        return [
            'chat_ids' => $this->chat_ids,
        ];
    }
}
