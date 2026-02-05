<?php

// namespace App\Http\Middleware;

// use App\Models\User;
// use Closure;
// use Illuminate\Http\Request;

// class ChatPermission
// {
//     public function handle(Request $request, Closure $next)
//     {
//         $me = auth()->user();
//         $partnerId = $request->route('partnerId');
//         $partner = User::findOrFail($partnerId);

//         if (!$me->isAdmin() && !$partner->isAdmin()) {
//             abort(403, 'このユーザーとはチャットできません');
//         }

//         return $next($request);
//     }
// }




namespace App\Http\Middleware;

use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use Closure;
use Illuminate\Http\Request;

class ChatPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $room_id = $request->route('room_id');

        // ルーム存在確認
        $chat_room = ChatRoom::find($room_id);
        if (!$chat_room) {
            abort(404);
        }

        // 参加者チェック（中間テーブルで判定）
        $room_user = ChatRoomUser::where('room_id', $room_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$room_user) {
            abort(403, 'このチャットルームに参加していません');
        }

        // 退室済みユーザーチェック
        if ($room_user->left_at !== null) {
            abort(403, '退室済みのチャットルームです');
        }

        // 管理者が一名以上存在するかチェック
        $has_admin = ChatRoomUser::where('room_id', $room_id)
            ->whereNull('left_at')
            ->whereHas('user', function ($q) {
                $q->where('admin_flg', 1);
            })
            ->exists();
            
        if (!$has_admin) {
            abort(403, '不正なチャットルームです');
        }


        return $next($request);
    }
}
