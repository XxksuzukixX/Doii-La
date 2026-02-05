<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;


class ReservationSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 
        'capacity', 
        'current_count', 
        'start_at', 
        'end_at'
    ];
    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function (ReservationSlot $slot) {

            // 紐づく予約ユーザーをキャンセル扱いにする
            $slot->reservationUsers()
                ->where('status', 'reserved')
                ->update([
                    'status' => 'canceled',
                ]);
        });
    }

    // 親の予約
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    // 予約ユーザー
    public function reservationUsers()
    {
        return $this->hasMany(ReservationUser::class, 'slot_id');
    }
    // 予約済ユーザー
    public function reservedUsers()
    {
        return $this->hasMany(ReservationUser::class, 'slot_id')
            ->where('status','reserved');
    }


    // 予約ユーザー経由でユーザー情報
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'reservation_users',
            'slot_id',
            'user_id'
        )
        ->withPivot('status')
        ->wherePivot('status', 'reserved')// statusがreservedのレコードのみ抽出
        ->withTimestamps();
    }
    // ログインユーザーがこの枠を予約済みか 
    public function isReservedBy(?int $userId = null): bool
    {
        $userId ??= Auth::id();

        return $this->reservationUsers
            ->where('user_id', $userId)
            ->where('status', 'reserved')
            ->isNotEmpty();
    }

    // // 満員かどうか
    // public function isFull(): bool
    // {
    //     return $this->reservationUsers
    //         ->where('status', 'reserved')
    //         ->count() >= $this->capacity;
    // }
    // 自分を除いた満員判定
    public function isFullForUser(?int $userId = null): bool
    {
        $userId ??= Auth::id();

        // 自分以外の予約済みユーザー数
        $reservedCount = $this->reservationUsers
            ->where('status', 'reserved')
            ->reject(fn($r) => $r->user_id === $userId)
            ->count();

        return $reservedCount >= $this->capacity;
    }
    // 予約人数の再計算
    public function recalcCurrentCount(): void
    {
        $this->current_count = $this->reservationUsers()
            ->where('status', 'reserved')
            ->count();

        $this->save();
    }


    // 日付差の表示
    public function getPeriodLabelAttribute(): string
    {
        $start = Carbon::parse($this->start_at);
        $end   = Carbon::parse($this->end_at);

        // 同年・同月・同日
        if ($start->isSameDay($end)) {
            return $start->format('Y年n月j日 H:i')
                . ' ~ '
                . $end->format('H:i');
        }

        // 同年・同月（別日）
        if ($start->isSameMonth($end)) {
            return $start->format('Y年n月j日 H:i')
                . ' ~ '
                . $end->format('j日 H:i');
        }

        // 同年（別月）
        if ($start->isSameYear($end)) {
            return $start->format('Y年n月j日 H:i')
                . ' ~ '
                . $end->format('n月j日 H:i');
        }

        // 別年
        return $start->format('Y年n月j日 H:i')
            . ' ~ '
            . $end->format('Y年n月j日 H:i');
    }
        // 日付差の表示
    public function getDisplayEndTimeAttribute(): string
    {
        $start = Carbon::parse($this->start_at);
        $end   = Carbon::parse($this->end_at);

        // 同年・同月・同日
        if ($start->isSameDay($end)) {
            return $end->format('H:i');
        }

        // 同年・同月（別日）
        if ($start->isSameMonth($end)) {
            return $end->format('n月j日 H:i');
        }

        // 同年（別月）
        if ($start->isSameYear($end)) {
            return $end->format('n月j日 H:i');
        }

        // 別年
        return $end->format('Y年n月j日 H:i');
    }
}