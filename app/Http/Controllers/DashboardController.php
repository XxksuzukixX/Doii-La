<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\Chat;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ChatRoomUser;

class DashboardController extends Controller
{
    /**
     * ダッシュボード表示
     */

    public function index()
    {
        $user = auth()->user();
        $my_user_id =$user->id;

        $slots = auth()->user()
            ->reservationSlots()
            ->wherePivot('status', 'reserved') //reservation_usersテーブルのステータスで抽出
            ->whereHas('reservation', function ($q) {
                $q->whereNull('deleted_at')    // SoftDeleteされていない予約
                ->where('status', 'published') // 公開されている予約
                ->orWhere('status', 'expired');
            })
            ->where('end_at', '>=', now())  // 終了時間が未来の予約
            // ->with('reservation')// 親予約
            ->with(['reservation.purpose']) // ← reservationとpurpose を eager load
            ->get();

        //未読募集カウント
        $unread_reservation_count = Reservation::query()
            ->where('status', 'published')
            ->where('deadline_at', '>=', now())
            ->whereDoesntHave('readers', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->count();

        //未読メッセージカウント
        $unread_message_count  = Chat::join('chat_room_users', 'chats.room_id', '=', 'chat_room_users.room_id')
            ->where('chat_room_users.user_id', $my_user_id)
            ->whereColumn('chats.id', '>', 'chat_room_users.last_read_chat_id')
            ->count();
        // dd(config('services.slack.notifications.webhook_url'));
        // Http::post(config('services.slack.notifications.webhook_url'), [
        //     'text' => '予約管理アプリ Doii-La からのテスト通知です！ 🚀',
        // ]);
       
        return view('dashboard.index', compact('slots', 'unread_reservation_count', 'unread_message_count'));
    }
}