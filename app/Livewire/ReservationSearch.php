<?php

namespace App\Livewire;

use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Reservation;
use App\Models\ReservationRead;
use Illuminate\Support\Facades\Auth;


class ReservationSearch extends Component
{
    use WithPagination; 
    public string $keyword = ''; //キーワード検索
    public string $sortField = 'publish_at';   // デフォルトのソート対象
    public string $sortDirection = 'desc';      // デフォルト昇順
    #[Url]
    public ?string $statusFilter = ''; // 管理者用ステータスフィルター

    // 初期化
    public function mount(): void
    {
        // URL に値がない場合のみ初期値を入れる
        if (!request()->has('statusFilter')) {
            $this->statusFilter = Auth::user()->admin_flg
                ? 'published'
                : '';
        }
    }

    // ソート切替用メソッド
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            // 同じフィールドなら昇順↔降順を切り替え
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // 新しいフィールドなら昇順で設定
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage(); // ページングをリセット
    }

    public function updatingKeyword(): void
    {
        $this->resetPage();
    }
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(int $reservationId): void
    {
        // ReservationRead::where('reservation_id', $reservationId)
        //     ->where('user_id', Auth::id())
        //     ->whereNull('read_at')
        //     ->update([
        //         'read_at' => now(),
        //     ]);

        // Auth::user()->markReservationAsRead($reservationId);

        // dump(
        //     Auth::user()
        //         ->reservationReads()
        //         ->where('reservation_id', $reservationId)
        //         ->first()
        // );
    
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $query = Reservation::query()
            ->with([
                'myRead' => fn ($q) => $q->where('user_id', Auth::id()),
            ])
            ->when($this->keyword !== '', function ($q) {
                $q->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->keyword . '%')
                      ->orWhere('staff_name', 'like', '%' . $this->keyword . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        if (Auth::user()->admin_flg) {
            if ($this->statusFilter !== '') {
                $query->where('status', $this->statusFilter);
            }
            // フィルター未選択なら全ステータスをそのまま表示
        } else {
            // 一般ユーザーはpublished,expiredのみ
            $query
                ->where('status', 'published')
                ->orWhere('status', 'expired');
        }
                
        $reservations = $query
            ->paginate(10)
            ->withQueryString();

        return view('livewire.reservation-search', compact('reservations'));
    }


}
