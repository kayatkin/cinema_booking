<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\SeancesMovie;
use App\Models\HallPricing;
use App\Models\HallConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Создаем новый платеж и билет с общей стоимостью.
     */
    public function store(Request $request)
    {
        try {
            // Логирование сырых данных
            Log::info('Raw request data:', $request->all());

            // Явно преобразуем JSON-строку в массив
            if (is_string($request->input('seats'))) {
                $request->merge(['seats' => json_decode($request->input('seats'), true)]);
            }

            // Валидация данных
            $validated = $request->validate([
                'seances_movie_id' => 'required|exists:seances_movies,id',
                'seats' => 'required|array|min:1',
                'seats.*' => 'integer',
            ]);

            Log::info('Validated data:', $validated);

            // Проверяем доступность выбранных мест
            $unavailableSeats = $this->checkSeatAvailability($validated['seats'], $validated['seances_movie_id']);
            if (!empty($unavailableSeats)) {
                return back()->withErrors(['error' => 'Места ' . implode(', ', $unavailableSeats) . ' уже заняты.']);
            }

            // Находим информацию о сеансе
            $seance = SeancesMovie::findOrFail($validated['seances_movie_id']);
            $hallId = $seance->hall_id;

            // Рассчитываем общую стоимость
            $totalPrice = $this->calculateTotalPrice($validated['seats'], $hallId);

            // Создаем платеж
            $payment = $this->createPayment($validated['seances_movie_id']);
            $payment->update(['total_price' => $totalPrice]);

            // Создаем билет
            $ticket = $this->createTicket($payment->id, $validated['seats']);

            // Генерируем QR-код для билета
            $this->generateQrCode($ticket);

            // Перенаправляем пользователя на страницу подтверждения
            return redirect()->route('clients.payments.show', ['payment' => $payment->id])
                ->with('success', 'Бронирование успешно создано!');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании платежа:', ['message' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Произошла ошибка при создании бронирования.']);
        }
    }

    /**
     * Проверяем доступность мест.
     */
    private function checkSeatAvailability(array $seats, int $seanceId): array
    {
        $unavailableSeats = [];
        foreach ($seats as $seat) {
            if (!Ticket::isSeatAvailable($seat, $seanceId)) {
                $unavailableSeats[] = $seat;
            }
        }
        return $unavailableSeats;
    }

    /**
     * Создаем платеж.
     */
    private function createPayment(int $seanceId): Payment
    {
        return Payment::create([
            'seances_movie_id' => $seanceId,
            'total_price' => 0, // Временное значение
            'status' => 'pending',
        ]);
    }

    /**
     * Создаем билет.
     */
    private function createTicket(int $paymentId, array $seats): Ticket
    {
        return Ticket::create([
            'payment_id' => $paymentId,
            'seat_list' => implode(',', $seats), // Преобразуем массив в строку
            'unique_code' => Ticket::generateUniqueCode(),
            'status' => 'active',
        ]);
    }

    /**
     * Рассчитываем общую стоимость бронирования.
     */
    private function calculateTotalPrice(array $seats, int $hallId): float
    {
        // Проверяем, что массив $seats содержит числа
        if (empty($seats) || !is_array($seats) || !array_reduce($seats, fn($carry, $item) => $carry && is_numeric($item), true)) {
            Log::error('Invalid seats data:', ['seats' => $seats]);
            throw new \Exception('Некорректные данные о выбранных местах.');
        }

        // Получаем типы мест
        $seatTypes = HallConfiguration::where('hall_id', $hallId)
            ->whereIn('global_seat_number', $seats)
            ->pluck('seat_type', 'global_seat_number')
            ->toArray();

        // Проверяем, что все выбранные места существуют в конфигурации зала
        $missingSeats = array_diff($seats, array_keys($seatTypes));
        if (!empty($missingSeats)) {
            Log::error('Some seats are not configured in the hall:', ['hall_id' => $hallId, 'missing_seats' => $missingSeats]);
            throw new \Exception('Выбранные места ' . implode(', ', $missingSeats) . ' не настроены в конфигурации зала.');
        }

        // Получаем цены для типов мест
        $prices = HallPricing::where('hall_id', $hallId)
            ->whereIn('seat_type', array_unique($seatTypes))
            ->pluck('price', 'seat_type')
            ->toArray();

        // Рассчитываем общую стоимость
        $totalPrice = 0;
        foreach ($seats as $seat) {
            $seatType = $seatTypes[$seat] ?? 'standart';
            $seatPrice = $prices[$seatType] ?? 0;

            Log::info('Adding price for seat', ['seat' => $seat, 'seat_type' => $seatType, 'price' => $seatPrice]);
            $totalPrice += $seatPrice;
        }

        Log::info('Calculated total price:', ['total_price' => $totalPrice]);
        return $totalPrice;
    }

    /**
     * Генерируем QR-код для билета.
     */
    private function generateQrCode(Ticket $ticket): void
    {
        try {
            $ticket->generateQrCodeFile();
        } catch (\Exception $e) {
            Log::error('Ошибка при генерации QR-кода:', ['message' => $e->getMessage()]);
            throw new \Exception('Не удалось сгенерировать QR-код для билета.');
        }
    }
}
