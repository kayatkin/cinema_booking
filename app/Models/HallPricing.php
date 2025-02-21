<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallPricing extends Model
{
    protected $fillable = ['hall_id', 'seat_type', 'price'];

    // Отношение "многие к одному" с моделью Hall
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}
