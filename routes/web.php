<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;

// Страница выбора фильма и времени сеанса
Route::get('/', [ClientController::class, 'index'])->name('clients.seances');

// Страница выбора места в зале
Route::get('select-seat/{seance_id}', [ClientController::class, 'selectSeat'])->name('clients.select_seat');

// Создание платежа и билета
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

// Страница подтверждения бронирования
Route::get('/payments/{payment}', [ClientController::class, 'showPayment'])->name('clients.payments.show');

// Страница просмотра билета
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('clients.tickets.show');

// Стандартные маршруты аутентификации Laravel UI
Auth::routes();

// Маршруты для администраторов
// Страница входа для администратора
Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
// Выход из системы для администратора
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
// Защищенные маршруты админки
//Route::middleware(['admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    // Создание нового зала (удаление)
    Route::post('/admin/halls/store-simple', [AdminController::class, 'storeSimpleHall'])->name('admin.halls.store.simple');
    Route::delete('/admin/halls/{hall_id}', [AdminController::class, 'deleteHall'])->name('admin.halls.delete');
    // Конфигурация зала (типы мест)
    Route::post('/admin/halls/{hall_id}/configure', [AdminController::class, 'saveHallConfiguration'])->name('admin.halls.configure');
    Route::get('/admin/halls/{hall_id}/configuration', [AdminController::class, 'getHallConfiguration'])->name('admin.halls.get-configuration');
    // Конфигурация цен на типы мест
    Route::get('/admin/halls/{hall_id}/pricing', [AdminController::class, 'getHallPricing'])->name('admin.halls.get-pricing');
    Route::post('/admin/halls/{hall_id}/pricing', [AdminController::class, 'saveHallPricing'])->name('admin.halls.save-pricing');
    // Создание фильма
    Route::post('/admin/movies/create', [AdminController::class, 'createMovie'])->name('admin.movies.create');
    // Получение данных о фильме (редактирование)
    Route::get('/admin/movies/{movie_id}/edit', [AdminController::class, 'getMovieForEdit'])->name('admin.movies.get-for-edit');
    // Обновление данных о фильме
    Route::post('/admin/movies/{movie_id}/update', [AdminController::class, 'updateMovie'])->name('admin.movies.update');
    // Удаление фильма
    Route::delete('/admin/movies/{movie_id}/delete', [AdminController::class, 'deleteMovie'])->name('admin.movies.delete');
    // Управление сеткой сеансов
    // Создание нового сеанса
    Route::post('/admin/seances/create', [AdminController::class, 'createSeance'])->name('admin.seances.create');
    // Сохранение нескольких сеансов
    Route::post('/admin/seances/store', [AdminController::class, 'store'])->name('admin.seances.store');
    Route::post('/admin/seances/save', [AdminController::class, 'store'])->name('seances.save');
    // Получение списка сеансов
    Route::get('/admin/seances/load', [AdminController::class, 'loadSeances'])->name('admin.seances.load');
    // Удаление сеанса
    Route::delete('/admin/seances/{id}', [AdminController::class, 'deleteSeance'])->name('admin.seances.delete');
    // Обновление статуса продаж
    Route::post('/admin/toggle-sales', [AdminController::class, 'toggleSales'])->name('toggleSales');
//});
