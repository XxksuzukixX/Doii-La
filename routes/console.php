<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\UnreadMessageNotification;
use Carbon\Carbon;
use App\Mail\UnreadChatMail;
use App\Models\ChatRoomUser;
use App\Models\Chat;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::call(function () {
//     DB::table('recent_users')->delete();
// })->daily();

// 公開時刻に行う処理
Schedule::call(function () {
    DB::table('reservations')
        ->where('status', 'draft')
        ->whereNotNull('publish_at')
        ->where('publish_at', '<=', now())
        ->where('deadline_at', '>', now())
        ->update([
            'status' => 'published',
        ]);
})->everyMinute();

// 締切時刻に行う処理
Schedule::call(function () {
    DB::table('reservations')
        ->where('status', 'published')
        ->whereNotNull('deadline_at')
        ->where('deadline_at', '<=', now())
        ->where('status', '!=', 'expired')
        ->update([
            'status' => 'expired',
        ]);
})->everyMinute();

// 公開終了時刻に行う処理
Schedule::call(function () {
    DB::table('reservations')
        ->whereNotNull('close_at')
        ->where('close_at', '<=', now())
        ->whereIn('status', ['published', 'expired'])
        ->update(['status' => 'closed']);
})->everyMinute();

// 管理者宛のメッセージが5分未読なら通知
Schedule::call(function () {

    $threshold = Carbon::now()->subMinutes(5);

    $adminRoomUsers = ChatRoomUser::query()
        ->whereHas('user', fn ($q) => $q->where('admin_flg', 1))
        ->whereNull('left_at')

        // 未通知のメッセージがある
        ->whereHas('latestChat', function ($q) {
            $q->whereColumn(
                'chats.id',
                '>',
                'chat_room_users.last_notified_chat_id'
            )
            ->orWhereNull('chat_room_users.last_notified_chat_id');
        })
        // 通知後5分以上経過している
        ->where(function ($q) use ($threshold) {
            $q->whereNull('last_notified_at')
            ->orWhere('last_notified_at', '<=', $threshold);
        })

        ->with('latestChat')
        ->get();

    foreach ($adminRoomUsers as $aru) {

        // eager load された最新チャットをそのまま使う
        $chat = $aru->latestChat;

        if (! $chat) {
            continue;
        }
        // メッセージタイプ確認
        if ($chat->type !== 'message') {
            continue;
        }
        // 自分の送信メッセージならスキップ
        if ($chat->sender_id === $aru->user_id) {
            continue;
        }
        // 送信から5分経過していなければスキップ
        if ($chat->created_at->gt($threshold)) {
            continue;
        }

        $admin  = $aru->user;
        $sender = $chat->sender;
        $app_name = config('app.name');
        $message = "【{$app_name}】\n{$admin->name}さん、{$sender->name}さんから新着メッセージがあります。";

        $aru->update([
            'last_notified_at'      => now(),
            'last_notified_chat_id' => $chat->id,
        ]);
        dump($message);
        // slack通知
        Http::post(config('services.slack.notifications.webhook_url'), [
            'text' => $message,
        ]);
        //　メール送信処理
        // Mail::to($admin->email)->queue(
        //     new UnreadChatMail(
        //         $admin->id,
        //         $sender->id,
        //         $chat->id
        //     )
        // );
    }
    
})->everyMinute();