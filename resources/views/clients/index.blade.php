<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ИдёмВКино</title>
    <link rel="stylesheet" href="{{ asset('assets/client/css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/styles.css') }}">
    <link
        href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900&amp;subset=cyrillic,cyrillic-ext,latin-ext"
        rel="stylesheet">
</head>

<body>
    <header class="page-header">
        <h1 class="page-header__title">Идём<span>в</span>кино</h1>
    </header>
    <nav class="page-nav">
        <a class="page-nav__day page-nav__day_today" href="#">
            <span class="page-nav__day-week">Пн</span><span class="page-nav__day-number">31</span>
        </a>
        <a class="page-nav__day" href="#">
            <span class="page-nav__day-week">Вт</span><span class="page-nav__day-number">1</span>
        </a>
        <a class="page-nav__day page-nav__day_chosen" href="#">
            <span class="page-nav__day-week">Ср</span><span class="page-nav__day-number">2</span>
        </a>
        <a class="page-nav__day" href="#">
            <span class="page-nav__day-week">Чт</span><span class="page-nav__day-number">3</span>
        </a>
        <a class="page-nav__day" href="#">
            <span class="page-nav__day-week">Пт</span><span class="page-nav__day-number">4</span>
        </a>
        <a class="page-nav__day page-nav__day_weekend" href="#">
            <span class="page-nav__day-week">Сб</span><span class="page-nav__day-number">5</span>
        </a>
        <a class="page-nav__day page-nav__day_weekend" href="#">
            <span class="page-nav__day-week">Вс</span><span class="page-nav__day-number">6</span>
        </a>
        <a class="page-nav__day page-nav__day_next" href="#"></a>
    </nav>
    <main>
        @foreach($moviesWithSeances as $movieTitle => $seances)
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
                                <span class="movie__data-duration">{{ $seances->first()->movie->duration }} минут</span>
                                <span class="movie__data-origin">{{ $seances->first()->movie->origin }}</span>
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

                    @foreach($groupedSeances as $hallName => $hallSeances)
                        <div class="movie-seances__hall">
                            <h3 class="movie-seances__hall-title">{{ $hallName }}</h3>
                            <ul class="movie-seances__list">
                                @foreach($hallSeances as $seance)
                                    <li class="movie-seances__time-block">
                                        <a class="movie-seances__time"
                                            href="{{ route('clients.select_seat', ['seance_id' => $seance->id]) }}"
                                            onclick="console.log('Нажата кнопка с сеансом ID:', {{ $seance->id }});">
                                            {{ \Carbon\Carbon::parse($seance->start_time)->format('H:i') }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </section>
        @endforeach
    </main>
    <script>
        document.querySelectorAll('.movie-seances__time').forEach(item => {
            item.addEventListener('click', function (event) {
                console.log('Клик по ссылке:', this.href);
            });
        });
    </script>
</body>

</html>