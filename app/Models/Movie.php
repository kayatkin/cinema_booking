<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',       // Название фильма
        'duration',    // Длительность фильма в минутах
        'synopsis',    // Краткое описание фильма
        'origin',      // Страна производства
        'poster_path', // Путь к постеру фильма
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
}
