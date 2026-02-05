<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'image_path'];

    // Reservationとのリレーション
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // 画像URL取得アクセサ
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
