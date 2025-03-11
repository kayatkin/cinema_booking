<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SeancesMovie extends Model
{
    /**
     * Атрибуты, которые можно массово назначать.
     */
    protected $fillable = [
        'movie_id',    // ID фильма
        'hall_id',     // ID зала
        'start_time',  // Время начала сеанса
        'end_time',    // Время окончания сеанса
    ];

    /**
     * Даты, которые должны быть преобразованы в объект Carbon.
     */
    protected $dates = ['start_time', 'end_time'];

    /**
     * Преобразование типов данных.
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Кэш цен
    protected $pricesCache = [];

    /**
     * Отношение "многие к одному" с моделью Movie.
     */
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Отношение "многие к одному" с моделью Hall.
     */
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Отношение "один ко многим" с моделью Payment.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Отношение "один ко многим" с моделью Ticket.
     */
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

    /**
     * Проверка, находится ли сеанс в указанном периоде.
     *
     * @param Carbon $startDate Начальная дата периода.
     * @param Carbon $endDate Конечная дата периода.
     * @return bool
     */
    public function isInPeriod(Carbon $startDate, Carbon $endDate): bool
    {
        return $this->start_time->greaterThanOrEqualTo($startDate) &&
               $this->start_time->lessThanOrEqualTo($endDate);
    }

    /**
     * Форматирование времени начала сеанса в формат HH:MM.
     *
     * @return string
     */
    public function getStartTimeFormattedAttribute(): string
    {
        return $this->start_time->format('H:i');
    }

    /**
     * Форматирование времени окончания сеанса в формат HH:MM.
     *
     * @return string
     */
    public function getEndTimeFormattedAttribute(): string
    {
        return $this->end_time->format('H:i');
    }
}