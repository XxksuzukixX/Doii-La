<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRead extends Model
{
    
    protected $fillable = [
        'user_id',
        'reservation_id',
        'read_at',
    ];

    // ユーザー
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // 対応する予約
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }


}
