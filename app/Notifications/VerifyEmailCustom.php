<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailCustom extends VerifyEmail
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('メールアドレス認証のお願い')
            ->greeting('ご登録ありがとうございます')
            ->line('以下のボタンをクリックして、メールアドレスの認証を完了してください。')
            ->action('メールアドレスを認証する', $url)
            ->line('このメールに心当たりがない場合は、破棄してください。')
            ->salutation(config('app.name') . ' 運営チーム');
    }
}
