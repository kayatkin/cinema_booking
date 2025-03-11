<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Movie extends Model
{
    /**
     * Атрибуты, которые можно массово назначать.
     */
    protected $fillable = [
        'title',               // Название фильма
        'duration',            // Длительность фильма в минутах
        'synopsis',            // Краткое описание фильма
        'origin',              // Страна производства
        'poster_path',         // Путь к постеру фильма
        'start_of_release',    // Начало проката (дата)
        'end_of_release',      // Окончание проката (дата)
    ];

    /**
     * Отношение "многие к одному" с моделью SeancesMovie.
     */
    public function seances()
    {
        return $this->hasMany(SeancesMovie::class);
    }

    /**
     * Получение полного пути к постеру фильма.
     */
    public function getPosterUrlAttribute()
    {
        return $this->poster_path ? asset('storage/' . $this->poster_path) : null;
    }

    /**
     * Мутатор для преобразования start_of_release в объект Carbon.
     */
    public function setStartOfReleaseAttribute($value)
    {
        $this->attributes['start_of_release'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    /**
     * Мутатор для преобразования end_of_release в объект Carbon.
     */
    public function setEndOfReleaseAttribute($value)
    {
        $this->attributes['end_of_release'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    /**
     * Получение начальной даты проката как объект Carbon.
     */
    public function getStartOfReleaseDateAttribute()
    {
        return $this->start_of_release ? Carbon::parse($this->start_of_release) : null;
    }

    /**
     * Получение конечной даты проката как объект Carbon.
     */
    public function getEndOfReleaseDateAttribute()
    {
        return $this->end_of_release ? Carbon::parse($this->end_of_release) : null;
    }

    /**
     * Проверка, находится ли текущая дата в периоде проката фильма.
     */
    public function isCurrentlyOnRelease()
    {
        $today = Carbon::today();
        $start = $this->getStartOfReleaseDateAttribute();
        $end = $this->getEndOfReleaseDateAttribute();

        return $start && $end && $today->greaterThanOrEqualTo($start) && $today->lessThanOrEqualTo($end);
    }
}