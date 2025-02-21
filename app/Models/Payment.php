<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class Payment extends Model
{
    protected $fillable = [
        'seances_movie_id',
        'total_price',
        'status',
    ];

    // Связь с моделью SeancesMovie
    public function seancesMovie()
    {
        return $this->belongsTo(SeancesMovie::class, 'seances_movie_id');
    }

    // Связь с моделью Ticket
    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    // Методы для проверки статуса
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    // Метод для обновления статуса
    public function updateStatus(string $newStatus): void
    {
        $allowedStatuses = ['pending', 'paid', 'canceled'];
        if (in_array($newStatus, $allowedStatuses)) {
            Log::info('Payment status updated', ['payment_id' => $this->id, 'old_status' => $this->status, 'new_status' => $newStatus]);
            $this->update(['status' => $newStatus]);
        } else {
            throw new \InvalidArgumentException("Недопустимый статус платежа: $newStatus");
        }
    }

    // Метод для получения информации о сеансе
    public function getSeanceInfo(): array
    {
        if (!$this->seancesMovie) {
            Log::error('Seance not found for payment:', ['payment_id' => $this->id]);
            return [];
        }
        return [
            'movie_title' => $this->seancesMovie->movie->title,
            'hall_name' => $this->seancesMovie->hall->name,
            'start_time' => $this->seancesMovie->start_time->format('H:i'),
        ];
    }

    // Метод для получения списка мест
    public function getSeatList(): array
    {
        if (!$this->ticket) {
            Log::warning('No ticket found for payment:', ['payment_id' => $this->id]);
            return [];
        }
        return $this->ticket->getSeatsArray();
    }

    // Метод для расчета общей стоимости
    public function calculateTotalPrice(): float
    {
        if (!$this->ticket || !$this->ticket->seat_list) {
            Log::warning('Ticket or seat list not found for payment:', ['payment_id' => $this->id]);
            return 0.0;
        }
        return $this->ticket->getTotalPrice();
    }

    // Метод для отмены платежа
    public function cancel(): void
    {
        if ($this->isCanceled()) {
            return; // Платеж уже отменен
        }
        $this->updateStatus('canceled');
        if ($this->ticket) {
            $this->ticket->cancel();
        }
        Log::info('Payment canceled', ['payment_id' => $this->id]);
    }
}
