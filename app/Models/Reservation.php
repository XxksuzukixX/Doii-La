<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'purpose_id',
        'title',
        'staff_name', 
        'description', 
        'status', 
        'publish_at', 
        'deadline_at',
        'close_at',
    ];
    protected $casts = [
        'publish_at'  => 'datetime',
        'deadline_at' => 'datetime',
        'close_at'    => 'datetime',
    ];

    // 予約枠
    public function slots()
    {
        return $this->hasMany(ReservationSlot::class, 'reservation_id');
    }
    // 予約枠
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    // 変更履歴
    public function histories()
    {
        return $this->hasMany(ReservationHistory::class);
    }
    // 既読ユーザー
    public function readers()
    {
        return $this->belongsToMany(User::class, 'reservation_reads')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }
    // 募集カテゴリー
    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }
    // ログインユーザー用（1件）
    public function myRead()
    {
        return $this->hasOne(ReservationRead::class)
            ->where('user_id', auth()->id());
    }

    
    // 公開前状態
    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }
    // 非公開状態
    public function getIsUnpublishedAttribute(): bool
    {
        return $this->status === 'unpublished';
    }
    // 公開状態
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }
    // 公開終了状態
    public function getIsClosedAttribute(): bool
    {
        return $this->status === 'closed';
    }
    // 受付終了状態
    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'expired';
    }
    // 実効的なステータス
    public function getEffectiveStatusAttribute(): string
    {
        // 公開日時が未来なら必ず draft 扱い
        if (
            $this->publish_at !== null &&
            Carbon::now()->lt($this->publish_at)
        ) {
            return 'draft';
        }

        return $this->status;
    }

    // 日付差の表示
    public function getPeriodLabelAttribute(): string
    {
        $start = Carbon::parse($this->publish_at);
        $end   = Carbon::parse($this->deadline_at);

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
}