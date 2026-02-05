<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnreadChatMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $receiverId,
        public int $senderId,
        public int $chatId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【Doii-La】未読メッセージ通知',
        );
    }

    public function content(): Content
    {
        $receiver = User::findOrFail($this->receiverId);
        $sender   = User::findOrFail($this->senderId);
        $chat     = Chat::findOrFail($this->chatId);
        return new Content(
            view: 'emails.unread_chat',
            with: compact('receiver', 'sender', 'chat'),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
