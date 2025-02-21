<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ИдёмВКино</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/styles.css') }}">
    <link
        href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900&amp;subset=cyrillic,cyrillic-ext,latin-ext"
        rel="stylesheet">
</head>

<body>
    <header class="page-header">
        <h1 class="page-header__title" onclick="window.location.href='{{ route('clients.seances') }}'"
            style="cursor: pointer;">
            Идём<span>в</span>кино
        </h1>
    </header>

    <main>
        <section class="buying">
            <div class="buying__info">
                <div class="buying__info-description">
                    <!-- Информация о фильме -->
                    <h2 class="buying__info-title">{{ $seance->movie?->title ?? 'Неизвестный фильм' }}</h2>
                    <p class="buying__info-start">Начало сеанса:
                        {{ \Carbon\Carbon::parse($seance->start_time)->format('H:i') }}</p>
                    <p class="buying__info-hall">{{ $seance->hall?->name ?? 'Неизвестный зал' }}</p>
                </div>
                <div class="buying__info-hint">
                    <p>Тапните дважды,<br>чтобы увеличить</p>
                </div>
            </div>
            <!-- Схема зала -->
            <div class="buying-scheme">
                <div class="buying-scheme__wrapper">
                    @for ($row = 1; $row <= $rows; $row++)
                                    <div class="buying-scheme__row">
                                        @for ($seatInRow = 1; $seatInRow <= $seats_per_row; $seatInRow++)
                                                            @php
                                                                // Вычисляем глобальный номер места
                                                                $globalSeatNumber = ($row - 1) * $seats_per_row + $seatInRow;
                                                                // Находим конфигурацию для данного места
                                                                $seatConfig = $hallConfiguration[array_search($globalSeatNumber, array_column($hallConfiguration, 'global_seat'))] ?? null;
                                                                // Определяем класс места
                                                                $seatClass = 'buying-scheme__chair';
                                                                if ($seatConfig) {
                                                                    if ($seatConfig['type'] === 'vip') {
                                                                        $seatClass .= ' buying-scheme__chair_vip';
                                                                    } elseif ($seatConfig['type'] === 'disabled') {
                                                                        $seatClass .= ' buying-scheme__chair_disabled';
                                                                    } else {
                                                                        $seatClass .= ' buying-scheme__chair_standart';
                                                                    }
                                                                    // Проверяем, занято ли место
                                                                    if (in_array((string) $globalSeatNumber, $occupiedSeats)) {
                                                                        $seatClass .= ' buying-scheme__chair_taken';
                                                                    }
                                                                } else {
                                                                    $seatClass .= ' buying-scheme__chair_disabled'; // Если конфигурация не найдена, место отключено
                                                                }
                                                            @endphp
                                                            <span class="{{ $seatClass }}" data-seat="{{ $globalSeatNumber }}"></span>
                                        @endfor
                                    </div>
                    @endfor
                </div>
                <!-- Легенда -->
                <div class="buying-scheme__legend">
                    <div class="col">
                        <p class="buying-scheme__legend-price">
                            <span class="buying-scheme__chair buying-scheme__chair_standart"></span>
                            Свободно (<span
                                class="buying-scheme__legend-value">{{ $standartPrice ?? 'Цена не указана' }}</span>
                            руб)
                        </p>
                        <p class="buying-scheme__legend-price">
                            <span class="buying-scheme__chair buying-scheme__chair_vip"></span>
                            Свободно VIP (<span
                                class="buying-scheme__legend-value">{{ $vipPrice ?? 'Цена не указана' }}</span> руб)
                        </p>
                    </div>
                    <div class="col">
                        <p class="buying-scheme__legend-price">
                            <span class="buying-scheme__chair buying-scheme__chair_taken"></span>
                            Занято
                        </p>
                        <p class="buying-scheme__legend-price">
                            <span class="buying-scheme__chair buying-scheme__chair_selected"></span>
                            Выбрано
                        </p>
                    </div>
                </div>
            </div>
            <!-- Форма бронирования -->
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" id="seances_movie_id" name="seances_movie_id" value="{{ $seance->id }}">
                <input type="hidden" id="selected_seats" name="seats" value="">
                <button type="submit" class="acceptin-button" disabled>Забронировать</button>
            </form>
        </section>
    </main>
    <!-- Подключение внешнего скрипта -->
    <script src="{{ asset('assets/client/js/seat-selection.js') }}"></script>
</body>

</html>