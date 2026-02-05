<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\ReservationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    //募集一覧表示
    public function list(Request $request)
    {
        return view('reservation.list');
    }
    //募集詳細表示
    public function detail(Reservation $reservation)
    {
        return view('reservation.detail', compact('reservation'));
    }

    // 募集編集
    public function edit(Reservation $reservation)
    {
        // 編集作業中は非公開
        if ($reservation->is_published || $reservation->is_expired) {

            $reservation->update([
                'status' => 'unpublished',
            ]);

        }
        return view('reservation.edit', compact('reservation'));
    }

    // 募集削除
    public function destroy(Reservation $reservation)
    {
        $reservation = DB::transaction(function () use ($reservation) {
            // 削除履歴追加
            $reservation->histories()->create([
                'editor_id' => Auth::id(),
                'action'    => 'delete',
                'comment'   => '削除',
            ]);
            // 募集を削除
            $reservation->delete(); // deleted_at に現在時刻が入る
            return $reservation;
        });
        

        return redirect()
        ->route('reservation.list')
        ->with('success', '予約を削除しました');
    }
    //募集公開
    public function publish(Reservation $reservation)
    {
        if ($reservation->is_draft || $reservation->is_unpublished) {

            $reservation->update([
                'status' => 'published',
            ]);

            return redirect()
            ->route('reservation.detail', ['reservation' => $reservation->id])
            ->with('success', '募集を公開しました');
        }

        if ($reservation->is_published) {
            return redirect()
                ->route('reservation.detail', ['reservation' => $reservation->id])
                ->with('info', '既に公開中のため、処理は行われませんでした');
        }
        if ($reservation->is_closed) {
            return redirect()
                ->route('reservation.detail', ['reservation' => $reservation->id])
                ->with('info', '募集は既に終了しています');
        }

    }
    //募集非公開
    public function unpublish(Reservation $reservation)
    {
        // 募集終了は不可
        if ($reservation->is_closed) {
            return redirect()
                ->route('reservation.detail', $reservation)
                ->with('info', '募集は既に終了しています');
        }

        // すでに非公開 or 公開前
        if ($reservation->is_unpublished || $reservation->is_draft) {
            return redirect()
                ->route('reservation.detail', $reservation)
                ->with('info', '既に非公開のため、処理は行われませんでした');
        }

        // 公開中 → 非公開
        if ($reservation->is_published) {
            $reservation->update([
                'status' => 'unpublished',
            ]);

            return redirect()
                ->route('reservation.detail', $reservation)
                ->with('success', '募集を非公開にしました');
        }

        // 想定外ステータス
        return redirect()
            ->route('reservation.detail', $reservation)
            ->with('error', '不正な状態です');
    }

    //予約
    public function reserve(ReservationSlot $slot)
    {
        $userId = auth()->id();

        DB::transaction(function () use ($slot, $userId) {
            
            // 募集が公開中かチェック
            if (! $slot->reservation->is_published) {
                abort(409, '非公開のためこの操作を行うことができません');
            }

            // すでに「予約中(reserved)」かチェック
            $already_reserved = ReservationUser::where('slot_id', $slot->id)
                ->where('user_id', $userId)
                ->where('status', 'reserved')
                ->exists();

            if ($already_reserved) {
                abort(409, 'すでに予約しています');
            }

            // 定員チェック
            if ($slot->current_count >= $slot->capacity) {
                // abort(400, '定員に達しています');
                abort(409, '満員のため予約できません');
            }

            // 過去のキャンセル履歴を取得
            $reservation_user = ReservationUser::where('slot_id', $slot->id)
                ->where('user_id', $userId)
                ->where('status', 'canceled')
                ->first();

            if ($reservation_user) {
                // 再予約（UPDATE）
                $reservation_user->update([
                    'status' => 'reserved',
                ]);
            } else {
                // 初回予約（INSERT）
                ReservationUser::create([
                    'slot_id' => $slot->id,
                    'user_id' => $userId,
                    'status'  => 'reserved',
                ]);
            }
            // current_count を再計算して更新
            $slot->recalcCurrentCount();
        });

        return back()->with('success', '予約が完了しました');
    }
    //予約キャンセル
    public function cancel(ReservationSlot $slot)
    {
        $userId = auth()->id();

        DB::transaction(function () use ($slot, $userId) {

            // 募集が公開中かチェック
            if (! $slot->reservation->is_published) {
                abort(409, '非公開のためこの操作を行うことができません');
            }

            // 自分の予約を取得
            $reservation_user = ReservationUser::where('slot_id', $slot->id)
                ->where('user_id', $userId)
                ->where('status', 'reserved')
                ->first();

            if (! $reservation_user) {
                abort(404, '予約が見つかりません');
            }

            // ステータス更新（論理キャンセル）
            $reservation_user->update([
                'status' => 'canceled',
            ]);

            // current_count を再計算して更新
            $slot->recalcCurrentCount();
        });

        return back()->with('success', '予約をキャンセルしました');
    }


}