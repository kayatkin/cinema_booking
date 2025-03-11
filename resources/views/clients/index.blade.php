<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ИдёмВКино</title>
    <link rel="stylesheet" href="{{ asset('assets/client/css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/styles.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900&amp;subset=cyrillic,cyrillic-ext,latin-ext" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <header class="page-header">
        <h1 class="page-header__title">Идём<span>в</span>кино</h1>
    </header>

  <!-- Навигация по датам -->
<nav class="page-nav">
    <!-- Кнопка "Предыдущий день" -->
    <a class="page-nav__day page-nav__day_prev" href="#"></a>

    <!-- Сегодняшний день -->
    @php
        $today = now()->format('Y-m-d');
    @endphp
    <a class="page-nav__day page-nav__day_today {{ now()->isWeekend() ? 'page-nav__day_weekend' : '' }} page-nav__day_chosen"
       href="#" data-date="{{ $today }}">
        <span class="page-nav__day-week">{{ now()->locale('ru')->isoFormat('dd') }}</span>
        <span class="page-nav__day-number">{{ now()->format('d') }}</span>
    </a>

    <!-- Следующие дни -->
    @for ($i = 1; $i <= 6; $i++)
        @php
            $date = now()->addDays($i);
            $isWeekend = $date->isWeekend();
            $formattedDate = $date->format('Y-m-d');
        @endphp
        <a class="page-nav__day {{ $isWeekend ? 'page-nav__day_weekend' : '' }}" 
           href="#" data-date="{{ $formattedDate }}">
            <span class="page-nav__day-week">{{ $date->locale('ru')->isoFormat('dd') }}</span>
            <span class="page-nav__day-number">{{ $date->format('d') }}</span>
        </a>
    @endfor

    <!-- Кнопка "Следующий день" -->
    <a class="page-nav__day page-nav__day_next" href="#"></a>
</nav>


    <!-- Основное содержимое -->
    <main id="movies-container">
        @if (isset($moviesWithSeances) && count($moviesWithSeances) > 0)
            @foreach ($moviesWithSeances as $movieTitle => $seances)
                <section class="movie">
                    <div class="movie__info">
                        <div class="movie__poster">
                            <img class="movie__poster-image" alt="{{ $movieTitle }} постер"
                                src="{{ asset('storage/' . $seances->first()->movie->poster_path) }}">
                        </div>
                        <div class="movie__description">
                            <h2 class="movie__title">{{ $movieTitle }}</h2>
                            <p class="movie__synopsis">{{ $seances->first()->movie->synopsis }}</p>
                            <p class="movie__data">
                                <span class="movie__data-duration">{{ $seances->first()->movie->duration }} минут </span>
                                <span class="movie__data-origin">{{ $seances->first()->movie->origin }} </span>
                            </p>
                        </div>
                    </div>

                    <!-- Группировка сеансов по залам -->
                    @php
                        $groupedSeances = [];
                        foreach ($seances as $seance) {
                            $hallName = $seance->hall->name;
                            if (!isset($groupedSeances[$hallName])) {
                                $groupedSeances[$hallName] = [];
                            }
                            $groupedSeances[$hallName][] = $seance;
                        }
                    @endphp

                    @foreach ($groupedSeances as $hallName => $hallSeances)
                        <div class="movie-seances__hall">
                            <h3 class="movie-seances__hall-title">{{ $hallName }}</h3>
                            <ul class="movie-seances__list">
                                @foreach ($hallSeances as $seance)
                                    <li class="movie-seances__time-block">
                                        <a class="movie-seances__time"
                                            href="{{ route('clients.select_seat', ['seance_id' => $seance->id]) }}"
                                            onclick="console.log('Нажата кнопка с сеансом ID:', {{ $seance->id }});">
                                            {{ Carbon\Carbon::parse($seance->start_time)->format('H:i') }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </section>
            @endforeach
        @else
            <p>На эту дату сеансы не запланированы.</p>
        @endif
    </main>

    <!-- Подключение внешнего JavaScript-файла -->
    <script src="{{ asset('assets/client/js/client.js') }}"></script>
</body>

</html>