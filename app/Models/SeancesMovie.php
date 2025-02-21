<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SeancesMovie extends Model
{
    protected $fillable = ['movie_id', 'hall_id', 'start_time', 'end_time'];
    protected $dates = ['start_time', 'end_time'];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    // Кэш цен
    protected $pricesCache = [];

    // Отношение "многие к одному" с моделью Movie
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    // Отношение "многие к одному" с моделью Hall
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    // Отношение "один ко многим" с моделью Payment
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Отношение "один ко многим" с моделью Ticket
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Получаем цену стандартного места для данного сеанса.
     *
     * @return float|null
     */
    public function getStandartPrice(): ?float
    {
        if (!$this->hall) {
            Log::error('Hall not found for seance:', ['seance_id' => $this->id]);
            return null;
        }

        if (!isset($this->pricesCache['standart'])) {
            $this->pricesCache['standart'] = $this->hall->pricings()
                ->where('seat_type', 'standart')
                ->value('price');
        }

        return $this->pricesCache['standart'];
    }

    /**
     * Получаем цену VIP-места для данного сеанса.
     *
     * @return float|null
     */
    public function getVipPrice(): ?float
    {
        if (!$this->hall) {
            Log::error('Hall not found for seance:', ['seance_id' => $this->id]);
            return null;
        }

        if (!isset($this->pricesCache['vip'])) {
            $this->pricesCache['vip'] = $this->hall->pricings()
                ->where('seat_type', 'vip')
                ->value('price');
        }

        return $this->pricesCache['vip'];
    }
}
