<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReservationPublished
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $reservation = $request->route('reservation');

        // 管理者は常に許可
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // // 一般ユーザーは公開済みのみ
        // if (!$reservation->is_published) {
        //     abort(403, 'この予約は公開されていません');
        // }

        // // published 以外は拒否
        // if ($reservation->effective_status !== 'published') {
        //     abort(403, 'この予約は現在公開されていません');
        // }
        // 一般ユーザーが閲覧可能な状態
        if (!in_array($reservation->effective_status, ['published', 'expired'], true)) {
            abort(403, 'この予約は現在公開されていません');
        }

        return $next($request);
    }
}
