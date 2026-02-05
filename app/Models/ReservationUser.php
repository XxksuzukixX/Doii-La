<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationUser extends Model
{
    use HasFactory;

    protected $fillable = ['slot_id', 'user_id', 'status'];

    // 予約枠
    public function slot()
    {
        return $this->belongsTo(ReservationSlot::class, 'slot_id');
    }

    // ユーザー
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}