<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    protected $fillable = ['name', 'rows', 'seats_per_row', 'is_active'];

    // Отношение "многие к одному" с моделью HallConfiguration
    public function configurations()
    {
        return $this->hasMany(HallConfiguration::class);
    }

    // Отношение "многие к одному" с моделью HallPricing
    public function pricings()
    {
        return $this->hasMany(HallPricing::class);
    }

    // Отношение "многие к одному" с моделью SeancesMovie
    public function seancesMovies()
    {
        return $this->hasMany(SeancesMovie::class, 'hall_id');
    }

    /**
     * Возвращаем текущую конфигурацию зала.
     *
     * @return array
     */
    public function getHallConfiguration()
    {
        // Получаем конфигурации мест
        $configuration = $this->configurations()->get()->map(function ($seat) {
            return [
                'global_seat' => $seat->global_seat_number,
                'type' => $seat->seat_type,
            ];
        })->toArray();

        // Возвращаем данные о конфигурации
        return [
            'rows' => $this->rows,
            'seats_per_row' => $this->seats_per_row,
            'configuration' => $configuration,
        ];
    }
}
