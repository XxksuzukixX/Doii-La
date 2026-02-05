<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\ReservationUser;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReservationDetail extends Component
{
    public Reservation $reservation;
    public $userId;
    public $isAdmin;
    public array $modalUserIds = [];

    public function mount(Reservation $reservation)
    {
        $this->reservation = $reservation;

        // スロット情報と予約状況をロード
        $this->reservation->load('slots.reservationUsers');

        $userId = Auth::id();
        $this->reservation->slots->each(function ($slot) use ($userId) {
            $slot->isReserved = $slot->isReservedBy($userId);
            $slot->isFull     = $slot->isFullForUser($this->userId); 
        });

        // 既読処理を User モデルに委譲
        Auth::user()->markReservationAsRead($this->reservation->id);
    }
    public function editReservation(Reservation $reservation)
    {
        // そのまま編集ページにリダイレクト
        return redirect()->route('reservation.edit', $reservation);
    }

    // モーダル用のユーザーIDをセット
    public function getReservedUser(int $slotId)
    {
        $slot = $this->reservation->slots->find($slotId);

        // 予約済みユーザーIDのみ取得
        $this->modalUserIds = $slot->users->pluck('id')->toArray();
        // UserSelectModal を開くイベントを発火
        $this->dispatch('open-reserved-user-modal', [
            'userIds' => $this->modalUserIds
        ]);
        $this->refreshSlots();
    }

    public function refreshSlots()
    {
        $this->reservation->slots->each(function ($slot) {
            $slot->isReserved = $slot->isReservedBy($this->userId);
            $slot->isFull = $slot->isFullForUser($this->userId); 
        });
    }
    
    public function publish()
    {
        // if ($this->reservation->is_draft || $this->reservation->is_unpublished) {
        //     $this->reservation->update(['status' => 'published']);
        //     $this->reservation->refresh();
        //     $this->refreshSlots(); // ここで状態を最新化
            
        // }

        if (! ($this->reservation->is_draft || $this->reservation->is_unpublished)) {
            return;
        }

        $status = 'published';

        if (
            $this->reservation->deadline_at &&
            $this->reservation->deadline_at->lte(now())
        ) {
            $status = 'expired';
        }

        $this->reservation->update([
            'status' => $status,
        ]);

        $this->reservation->refresh();
        $this->refreshSlots();
    }

    public function unpublish()
    {
        if ($this->reservation->is_published || $this->reservation->is_expired) {
            $this->reservation->update(['status' => 'unpublished']);
            $this->reservation->refresh();
            $this->refreshSlots(); // ここで状態を最新化

        }
    }

    // public function reserve(ReservationSlot $slot)
    // {
    //     // 開始時刻チェック
    //     if ($slot->start_at && $slot->start_at->isPast()) {
    //         abort(409, 'この予約枠は受付終了しています');
    //     }
    //     DB::transaction(function () use ($slot) {

    //         $userId = $this->userId ?? Auth::id();

    //         if (!$slot->reservation->is_published) abort(409, '現在受付できません');
    //         if (ReservationUser::where('slot_id', $slot->id)
    //                 ->where('user_id', $this->userId)
    //                 ->where('status', 'reserved')
    //                 ->exists()
    //             ) {
    //             abort(409, 'すでに予約済みです');
    //         }
    //         $count = ReservationUser::where('slot_id', $slot->id)
    //             ->where('status', 'reserved')
    //             ->lockForUpdate()
    //             ->count();
    //         if ($count >= $slot->capacity) abort(409, '満員のため予約できません');

    //         $reservationUser = ReservationUser::firstOrNew([
    //             'slot_id' => $slot->id,
    //             'user_id' => $userId,
    //         ]);
    //         $reservationUser->status = 'reserved';
    //         $reservationUser->save();

    //         $slot->recalcCurrentCount();
    //     });

    //     $this->refreshSlots();
    // }

    public function reserve(ReservationSlot $slot)
    {
        DB::transaction(function () use ($slot) {

            $userId = $this->userId ?? Auth::id();

            // slot を行ロックして最新状態を取得
            $slot = ReservationSlot::where('id', $slot->id)
                ->lockForUpdate()
                ->first();

            // 開始時刻チェック（ロック後）
            if ($slot->start_at && $slot->start_at->isPast()) {
                abort(409, 'この予約枠は受付終了しています');
            }

            if (!$slot->reservation->is_published) {
                abort(409, '現在受付できません');
            }

            if (ReservationUser::where('slot_id', $slot->id)
                ->where('user_id', $userId)
                ->where('status', 'reserved')
                ->exists()) {
                abort(409, 'すでに予約済みです');
            }

            // 現在の予約人数を計算
            $count = ReservationUser::where('slot_id', $slot->id)
                ->where('status', 'reserved')
                ->count();

            if ($count >= $slot->capacity) {
                abort(409, '満員のため予約できません');
            }

            ReservationUser::updateOrCreate(
                [
                    'slot_id' => $slot->id,
                    'user_id' => $userId,
                ],
                [
                    'status' => 'reserved',
                ]
            );

            $slot->recalcCurrentCount();
        });

        $this->refreshSlots();
    }

    public function cancel(ReservationSlot $slot)
    {
        // 開始時刻チェック
        if ($slot->start_at && $slot->start_at->isPast()) {
            abort(409, 'この予約枠は受付終了しています');
        }
        DB::transaction(function () use ($slot) {

            $userId = $this->userId ?? Auth::id();
            if (!$slot->reservation->is_published) abort(409, '非公開のため操作できません');

            if (ReservationUser::where('slot_id', $slot->id)->where('user_id', $userId)->where('status', 'canceled')->exists()) {
                abort(409, 'すでにキャンセル済みです');
            }

            $reservationUser = ReservationUser::where('slot_id', $slot->id)
                ->where('user_id', $userId)
                ->where('status', 'reserved')
                ->first();

            if (!$reservationUser) abort(404, '予約が見つかりません');

            $reservationUser->update(['status' => 'canceled']);
            $slot->recalcCurrentCount();
        });

        $this->refreshSlots();
    }

    public function exportCsv(): StreamedResponse
    {
        $this->reservation->load([
            'creator',
            'slots.reservationUsers.user',
        ]);

        $fileName = sprintf(
            'reservation_%d_%s.csv',
            $this->reservation->id,
            now()->format('Ymd_His')
        );

        return response()->streamDownload(function () {

            $handle = fopen('php://output', 'w');

            // UTF-8 BOM（Excel対策）
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, [
                '募集ID',
                '募集タイトル',
                '担当者',
                '公開開始日時',
                '締切日時',
                '公開終了日時',
                '枠番号',
                '開始日時',
                '終了日時',
                '定員',
                '現在の予約人数',
                'ユーザーID',
                'ユーザー名',
                'メールアドレス',
                '予約日時',
            ]);

            // 募集行
            fputcsv($handle, [
                $this->reservation->id, // 募集ID
                $this->reservation->title,
                $this->reservation->staff_name,
                optional($this->reservation->publish_at)?->format('Y/m/d H:i'),
                optional($this->reservation->deadline_at)?->format('Y/m/d H:i'),
                optional($this->reservation->close_at)?->format('Y/m/d H:i'),
                '', '', '', '', '', '', '', '', '',
            ]);

            // 枠とユーザー情報
            $slots = $this->reservation->slots->sortBy('start_at')->values();
            foreach ($slots as $index => $slot) {
                // 枠行
                fputcsv($handle, [
                    '', '', '', '', '', '', // 募集IDやタイトルなどは空欄
                    $index + 1, // 枠番号
                    optional($slot->start_at)?->format('Y/m/d H:i'),
                    optional($slot->end_at)?->format('Y/m/d H:i'),
                    $slot->capacity,
                    $slot->current_count,
                    '', '', '', '',
                ]);

                // ユーザー行（キャンセル除外）
                foreach ($slot->reservationUsers->where('status', 'reserved') as $ru) {
                    fputcsv($handle, [
                        '', '', '', '', '', // 募集IDやタイトルなどは空欄
                        '', '', '', '', '', '',
                        $ru->user_id,
                        optional($ru->user)->name,
                        optional($ru->user)->email,
                        optional($ru->updated_at)?->format('Y/m/d H:i'),
                    ]);
                }
            }


            fclose($handle);

        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
    // pdf出力

    public function exportPdf()
    {
        $this->reservation->load([
            'creator',
            'slots.reservationUsers.user',
        ]);

        $fileName = sprintf(
            'reservation_%d_%s.pdf',
            $this->reservation->id,
            now()->format('Ymd_His')
        );

        $pdf = Pdf::loadView('pdf.reservation-detail', [
            'reservation' => $this->reservation,
        ])->setPaper('A4', 'portrait');

        // Dompdf インスタンス取得
        $dompdf = $pdf->getDomPDF();
        $fontMetrics = $dompdf->getFontMetrics();

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }



    public function render()
    {
        return view('livewire.reservation-detail');
    }

    // public function render()
    // {
    //     return view('livewire.reservation-detail', [
    //         'reservation' => $this->reservation,
    //     ]);
    // }
}