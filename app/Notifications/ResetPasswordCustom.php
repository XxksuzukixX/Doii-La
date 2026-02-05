<?php


namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCustom extends ResetPassword
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('パスワード再設定のご案内')
            ->greeting('ご利用ありがとうございます')
            ->line('以下のボタンからパスワードの再設定を行ってください。')
            ->action('パスワードを再設定する', $url)
            ->line('このメールに心当たりがない場合は、破棄してください。')
            ->salutation(config('app.name') . ' 運営チーム');
    }
}