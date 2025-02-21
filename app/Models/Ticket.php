<?php

namespace App\Models;

require_once app_path('Libraries/phpqrcode/qrlib.php');

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class Ticket extends Model
{
    protected $fillable = [
        'payment_id',       // ID платежа
        'seat_list',        // Список выбранных мест (например, "6,7")
        'unique_code',      // Уникальный код бронирования
        'status',           // Статус билета (например, "active", "used")
        'qr_code_path',     // Путь к QR-коду
    ];

    /**
     * Генерация уникального кода бронирования.
     *
     * @return string
     */
    public static function generateUniqueCode(): string
    {
        return 'TICKET_' . bin2hex(random_bytes(8)); // Пример: TICKET_1a2b3c4d5e6f7g8h
    }

    /**
     * Получение списка занятых мест для конкретного сеанса.
     *
     * @param int $seances_movie_id ID сеанса
     * @return array
     */
    public static function getOccupiedSeatsForSeance(int $seances_movie_id): array
    {
        return self::whereHas('payment', function ($query) use ($seances_movie_id) {
            $query->where('seances_movie_id', $seances_movie_id);
        })
            ->pluck('seat_list')
            ->map(fn($seatList) => explode(',', $seatList)) // Преобразуем строку в массив
            ->flatten() // Объединяем все массивы в один
            ->unique() // Удаляем дубликаты
            ->values() // Перенумеровываем индексы
            ->toArray();
    }

    /**
     * Проверка доступности места для конкретного сеанса.
     *
     * @param int $seat Номер места
     * @param int $seances_movie_id ID сеанса
     * @return bool
     */
    public static function isSeatAvailable(int $seat, int $seances_movie_id): bool
    {
        $occupiedSeats = self::getOccupiedSeatsForSeance($seances_movie_id);
        return !in_array($seat, $occupiedSeats, true);
    }

    /**
     * Отношение "многие к одному" с моделью Payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Отношение "многие к одному" с моделью SeancesMovie через Payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function seancesMovie()
    {
        return $this->hasOneThrough(
            SeancesMovie::class,
            Payment::class,
            'id', // Внешний ключ в таблице payments
            'id', // Внешний ключ в таблице seances_movies
            'payment_id', // Локальный ключ в таблице tickets
            'seances_movie_id' // Локальный ключ в таблице payments
        );
    }

    /**
     * Генерация QR-кода с использованием библиотеки phpqrcode.
     *
     * @return string
     * @throws Exception
     */
    public function generateQrCodeFile()
    {
        if (!$this->qr_code_path) {
            try {
                // Подключаем библиотеку
                require_once app_path('Libraries/phpqrcode/qrlib.php');

                // Получаем данные для QR-кода
                $qrCodeData = $this->generateQrCodeData();

                Log::info('Generating QR code for ticket:', ['ticket_id' => $this->id, 'data' => $qrCodeData]);

                // Путь к файлу QR-кода
                $qrCodePath = "qrcodes/{$this->unique_code}.png";

                // Создаем директорию, если она не существует
                if (!Storage::exists('qrcodes')) {
                    Storage::makeDirectory('qrcodes');
                }

                // Генерируем QR-код
                \QRcode::png(
                    $qrCodeData, // Данные для QR-кода
                    storage_path("app/public/$qrCodePath"), // Путь сохранения
                    'L', // Уровень коррекции ошибок
                    4, // Масштаб изображения
                    0 // Отступы вокруг QR-кода
                );

                // Сохраняем путь к QR-коду в базе данных
                $this->update(['qr_code_path' => $qrCodePath]);

                Log::info('QR code generated successfully:', ['ticket_id' => $this->id, 'path' => $qrCodePath]);
            } catch (Exception $e) {
                Log::error('QR code generation failed:', ['ticket_id' => $this->id, 'error' => $e->getMessage()]);
                throw new Exception('Ошибка при генерации QR-кода.');
            }
        }

        return $this->qr_code_path;
    }

    /**
     * Генерация данных для QR-кода.
     *
     * @return string
     */
    public function generateQrCodeData(): string
    {
        // Пример данных для QR-кода
        return 'ticket_id:' . $this->id . ';seance_id:' . optional($this->payment->seancesMovie)->id;
    }

    /**
     * Преобразование списка мест из строки в массив.
     *
     * @return array
     */
    public function getSeatsArray(): array
    {
        return $this->seat_list ? explode(',', $this->seat_list) : [];
    }

    /**
     * Преобразование массива мест в строку для сохранения.
     *
     * @param mixed $value Массив или строка с номерами мест
     */
    public function setSeatListAttribute($value)
    {
        $this->attributes['seat_list'] = implode(',', array_map('trim', (array)$value));
    }

    /**
     * Получение информации о типах мест из HallConfiguration.
     *
     * @return array
     */
    public function getSeatTypes(): array
    {
        $seatsArray = $this->getSeatsArray();
        $hallId = optional($this->seancesMovie)->hall_id;

        if (empty($hallId)) {
            Log::error('Hall ID not found for ticket:', ['ticket_id' => $this->id]);
            return [];
        }

        return HallConfiguration::whereIn('global_seat_number', $seatsArray)
            ->where('hall_id', $hallId)
            ->pluck('seat_type', 'global_seat_number') // Возвращаем массив [номер_места => тип_места]
            ->toArray();
    }

    /**
     * Получение общей стоимости билетов.
     *
     * @return float
     */
    public function getTotalPrice(): float
    {
        if (empty($this->seat_list)) {
            return 0.0;
        }

        $seats = explode(',', $this->seat_list);
        $seance = SeancesMovie::findOrFail($this->payment->seances_movie_id);
        $hallId = $seance->hall_id;

        $seatTypes = HallConfiguration::where('hall_id', $hallId)
            ->whereIn('global_seat_number', $seats)
            ->pluck('seat_type', 'global_seat_number')
            ->toArray();

        $prices = HallPricing::where('hall_id', $hallId)
            ->whereIn('seat_type', array_unique($seatTypes))
            ->pluck('price', 'seat_type')
            ->toArray();

        $totalPrice = 0;

        foreach ($seats as $seat) {
            $seatType = $seatTypes[$seat] ?? 'standart';
            $totalPrice += $prices[$seatType] ?? 0;
        }

        return $totalPrice;
    }

    /**
     * Отмена бронирования.
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);

        // Удаляем QR-код, если он существует
        if ($this->qr_code_path && Storage::exists($this->qr_code_path)) {
            Storage::delete($this->qr_code_path);
        }
    }

    /**
     * Получение информации о фильме, зале и времени сеанса.
     *
     * @return array
     */
    public function getMovieInfo(): array
    {
        if (!$this->seancesMovie) {
            Log::warning('Seance not found for ticket:', ['ticket_id' => $this->id]);
            return [];
        }

        return [
            'movie' => $this->seancesMovie->movie->title ?? 'Неизвестный фильм',
            'hall' => $this->seancesMovie->hall->name ?? 'Неизвестный зал',
            'start_time' => optional($this->seancesMovie->start_time)->format('H:i') ?? 'Время не указано',
        ];
    }
}
