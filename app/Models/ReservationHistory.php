<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationHistory extends Model
{
    protected $fillable = [
        'reservation_id',
        'editor_id',
        'action',
        'comment',
    ];

    /**
     * 対応する予約
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * 編集者（ユーザー）
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
