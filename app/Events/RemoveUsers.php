<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\Chat;

class RemoveUsers implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $remove_members;
    public Chat $chat;

    /**
     * Create a new event instance.
     */
    public function __construct(array $remove_members, Chat $chat)
    {
        $this->remove_members = $remove_members;
        $this->chat = $chat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(
            'chat.room.' . $this->chat->room_id
        );
    }
    public function broadcastWith(): array
    {
        return [
            'removed_users' => $this->remove_members,
        ];
    }
}
