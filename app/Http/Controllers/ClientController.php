<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\SeancesMovie;
use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Отображаем страницу списка сеансов.
     */
    public function index()
    {
        // Получаем все сеансы с связанными данными о фильмах и залах
        $seancesMovies = SeancesMovie::with('movie', 'hall')
            ->orderBy('start_time')
            ->paginate(50);

        // Группируем сеансы по фильмам для удобного отображения
        $moviesWithSeances = $seancesMovies->groupBy(function ($seance) {
            return $seance->movie->title; // Группировка по названию фильма
        });

        return view('clients.index', compact('moviesWithSeances', 'seancesMovies'));
    }

    /**
     * Отображаем страницу выбора места для указанного сеанса.
     *
     * @param int $seance_id ID сеанса
     */
    public function selectSeat($seance_id)
    {
        try {
            // Находим информацию о сеансе
            $seance = SeancesMovie::with('hall')->findOrFail($seance_id);

            if (!$seance->hall) {
                Log::error('Hall not found for seance:', ['seance_id' => $seance_id]);
                abort(404, 'Зал для данного сеанса не найден.');
            }

            // Получаем конфигурацию зала
            $hallConfiguration = $seance->hall->getHallConfiguration();

            // Проверяем, что конфигурация зала существует
            if (empty($hallConfiguration['configuration'])) {
                abort(404, 'Конфигурация зала не найдена.');
            }

            // Получаем список занятых мест для данного сеанса
            $occupiedSeats = Ticket::getOccupiedSeatsForSeance($seance_id);

            // Получаем цены через методы модели SeancesMovie
            $standartPrice = $seance->getStandartPrice();
            $vipPrice = $seance->getVipPrice();

            // Проверяем, что цены на билеты установлены
            if (is_null($standartPrice) || is_null($vipPrice)) {
                abort(500, 'Цены на билеты не установлены.');
            }

            // Логирование передаваемых данных
            Log::info('Data passed to seat selection page:', [
                'seance_id' => $seance->id,
                'hall_id' => $seance->hall->id,
                'occupied_seats' => $occupiedSeats,
                'standart_price' => $standartPrice,
                'vip_price' => $vipPrice,
            ]);

            // Передаем данные в шаблон
            return view('clients.select_seat', [
                'seance' => $seance,
                'hallConfiguration' => $hallConfiguration['configuration'] ?? [],
                'rows' => $hallConfiguration['rows'] ?? 0,
                'seats_per_row' => $hallConfiguration['seats_per_row'] ?? 0,
                'occupiedSeats' => $occupiedSeats,
                'standartPrice' => $standartPrice,
                'vipPrice' => $vipPrice,
            ]);
        } catch (\Exception $e) {
            // Логирование ошибки
            Log::error('Ошибка в методе selectSeat:', ['message' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Произошла ошибка при загрузке страницы выбора места.']);
        }
    }

    /**
     * Отображаем страницу подтверждения бронирования.
     *
     * @param Payment $payment Объект платежа
     * @return \Illuminate\View\View
     */
    public function showPayment($payment)
    {
        try {
            // Загружаем платеж с связанными данными
            $payment = Payment::with('ticket.seancesMovie')->find($payment);

            // Проверяем, найден ли платеж
            if (!$payment) {
                Log::error('Payment not found:', ['payment_id' => $payment]);
                abort(404, 'Платеж не найден.');
            }

            // Получаем связанный билет
            $ticket = $payment->ticket;
            if (!$ticket) {
                Log::error('Ticket not found for payment:', ['payment_id' => $payment]);
                abort(404, 'Билет не найден.');
            }

            // Получаем связанный сеанс
            $seance = $ticket->seancesMovie;
            if (!$seance) {
                Log::error('Seance not found for payment:', ['payment_id' => $payment]);
                abort(500, 'Сеанс для данного платежа не найден.');
            }

            // Передаем данные в шаблон
            return view('clients.payments.show', compact('payment', 'ticket', 'seance'));
        } catch (\Exception $e) {
            // Логирование ошибки
            Log::error('Ошибка в методе showPayment:', [
                'payment_id' => $payment,
                'message' => $e->getMessage(),
            ]);

            // Возвращаем представление с ошибкой
            return view('errors.error', [
                'errorCode' => 500,
                'errorMessage' => 'Произошла ошибка при загрузке страницы подтверждения бронирования.',
            ]);
        }
    }
}
