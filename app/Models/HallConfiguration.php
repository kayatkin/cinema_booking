<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallConfiguration extends Model
{
    protected $fillable = ['hall_id', 'global_seat_number', 'seat_type'];

    // Отношение "многие к одному" с моделью Hall
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}
