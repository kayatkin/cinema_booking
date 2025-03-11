<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ИдёмВКино — Администрирование</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/CSS/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/CSS/styles.css') }}">
</head>

<body>
    <header class="page-header">
        <h1 class="page-header__title">Идём<span>в</span>кино</h1>
        <span class="page-header__subtitle">Администраторская</span>
    </header>
    <main class="conf-steps">
        <!-- Управление залами -->
        <section class="conf-step">
            <header class="conf-step__header conf-step__header_opened">
                <h2 class="conf-step__title">Управление залами</h2>
            </header>
            <div class="conf-step__wrapper">
                <p class="conf-step__paragraph">Доступные залы:</p>
                <ul class="conf-step__list">
                    @foreach ($halls as $hall)
                        <li>{{ $hall->name }}
                            <button class="conf-step__button conf-step__button-trash"
                                data-hall-id="{{ $hall->id }}"></button>
                        </li>
                    @endforeach
                </ul>
                <button id="create-hall-button" class="conf-step__button conf-step__button-accent">Создать зал</button>
            </div>
        </section>

        <!-- Конфигурация залов -->
        <section class="conf-step">
            <header class="conf-step__header conf-step__header_opened">
                <h2 class="conf-step__title">Конфигурация залов</h2>
            </header>
            <div class="conf-step__wrapper">
                <div id="loading-indicator" style="display: none; text-align: center; margin-top: 20px;">
                    <span>Загрузка...</span>
                </div>
                <p class="conf-step__paragraph">Выберите зал для конфигурации:</p>
                <ul class="conf-step__selectors-box">
                    @foreach ($halls as $hall)
                        <li>
                            <input type="radio" class="conf-step__radio" name="chairs-hall" value="{{ $hall->id }}"
                                data-hall-id="{{ $hall->id }}">
                            <span class="conf-step__selector">{{ $hall->name }}</span>
                        </li>
                    @endforeach
                </ul>

                <!-- Секция конфигурации схемы зала -->
                <div id="hall-config-section" style="display: none;">
                    <p class="conf-step__paragraph">Укажите количество рядов и максимальное количество кресел в ряду:
                    </p>
                    <div class="conf-step__legend">
                        <label class="conf-step__label">Рядов, шт<input type="number" class="conf-step__input"
                                placeholder="10" id="rows-input" min="1"></label>
                        <span class="multiplier">x</span>
                        <label class="conf-step__label">Мест, шт<input type="number" class="conf-step__input"
                                placeholder="8" id="seats-per-row-input" min="1"></label>
                    </div>

                    <p class="conf-step__paragraph">Теперь вы можете указать типы кресел на схеме зала:</p>
                    <div class="conf-step__legend">
                        <span class="conf-step__chair conf-step__chair_standart"></span> — обычные кресла
                        <span class="conf-step__chair conf-step__chair_vip"></span> — VIP кресла
                        <span class="conf-step__chair conf-step__chair_disabled"></span> — заблокированные (нет кресла)
                        <p class="conf-step__hint">Чтобы изменить вид кресла, нажмите по нему левой кнопкой мыши</p>
                    </div>
                    <!-- Прогресс-бар перед схемой зала -->
                    <div id="progress-bar-container" style="display: none; margin-top: 20px;">
                        <div id="progress-bar"></div>
                    </div>
                    <div class="conf-step__hall">
                        <div class="conf-step__hall-wrapper" id="hall-scheme">
                            <!-- Схема зала будет генерироваться динамически -->
                        </div>
                    </div>

                    <fieldset class="conf-step__buttons text-center">
                        <button class="conf-step__button conf-step__button-regular"
                            id="cancel-hall-config-button">Отмена</button>
                        <button class="conf-step__button conf-step__button-accent"
                            id="save-hall-config-button">Сохранить</button>
                    </fieldset>
                </div>
            </div>
        </section>

        <!-- Конфигурация цен -->
        <section class="conf-step">
            <header class="conf-step__header conf-step__header_opened">
                <h2 class="conf-step__title">Конфигурация цен</h2>
            </header>
            <div class="conf-step__wrapper">
                <!-- Выбор зала -->
                <p class="conf-step__paragraph">Выберите зал для конфигурации:</p>
                <ul class="conf-step__selectors-box">
                    @foreach ($halls as $hall)
                        <li>
                            <input type="radio" class="conf-step__radio" name="prices-hall" value="{{ $hall->id }}"
                                data-hall-id="{{ $hall->id }}">
                            <span class="conf-step__selector">{{ $hall->name }}</span>
                        </li>
                    @endforeach
                </ul>

                <!-- Форма настройки цен -->
                <div id="pricing-config-section" style="display: none;">
                    <p class="conf-step__paragraph">Установите цены для типов кресел:</p>

                    <!-- Цена для обычных кресел -->
                    <div class="conf-step__legend">
                        <label class="conf-step__label">
                            Цена, рублей
                            <input type="text" class="conf-step__input" id="standart-price-input" placeholder="0">
                        </label>
                        за <span class="conf-step__chair conf-step__chair_standart"></span> обычные кресла
                    </div>

                    <!-- Цена для VIP-кресел -->
                    <div class="conf-step__legend">
                        <label class="conf-step__label">
                            Цена, рублей
                            <input type="text" class="conf-step__input" id="vip-price-input" placeholder="0">
                        </label>
                        за <span class="conf-step__chair conf-step__chair_vip"></span> VIP кресла
                    </div>

                    <!-- Кнопки управления -->
                    <fieldset class="conf-step__buttons text-center">
                        <button class="conf-step__button conf-step__button-regular"
                            id="cancel-pricing-button">Отмена</button>
                        <button class="conf-step__button conf-step__button-accent"
                            id="save-pricing-button">Сохранить</button>
                    </fieldset>
                </div>
            </div>
        </section>

        <!-- Сетка сеансов -->
        <section class="conf-step">
            <header class="conf-step__header conf-step__header_opened">
                <h2 class="conf-step__title">Сетка сеансов</h2>
            </header>
            <div class="conf-step__wrapper">

                <!-- Навигация по датам -->
                <div class="conf-step__date-navigation">
                    <button class="conf-step__button conf-step__button-regular" id="prev-day">← Предыдущий день</button>
                    <span class="conf-step__button conf-step__button-accent"
                        id="current-date">{{ date('Y-m-d') }}</span>
                    <button class="conf-step__button conf-step__button-regular" id="next-day">Следующий день →</button>
                </div>

                <!-- Добавление фильма -->
                <p class="conf-step__paragraph">
                    <button class="conf-step__button conf-step__button-accent" id="add-movie-button">
                        Добавить фильм
                    </button>
                </p>

                <!-- Список фильмов -->
                <div class="conf-step__movies">
                    @foreach ($movies as $movie)
                        <div class="conf-step__movie" data-movie-id="{{ $movie->id }}"
                            data-movie-title="{{ $movie->title }}" data-movie-duration="{{ $movie->duration }}"
                            data-movie-synopsis="{{ $movie->synopsis }}" data-movie-origin="{{ $movie->origin }}"
                            data-movie-poster="{{ $movie->poster_path }}" draggable="true">
                            <img class="conf-step__movie-poster" alt="{{ $movie->title }}"
                                src="{{ asset('storage/' . $movie->poster_path) }}">
                            <h3 class="conf-step__movie-title">{{ $movie->title }}</h3>
                            <p class="conf-step__movie-duration">{{ $movie->duration }} минут</p>
                        </div>
                    @endforeach
                </div>

                <!-- Сетка сеансов -->
                <div class="conf-step__seances">
                    @foreach ($halls as $hall)
                                        <div class="conf-step__seances-hall" data-hall-id="{{ $hall->id }}">
                                            <h3 class="conf-step__seances-title">{{ $hall->name }}</h3>

                                            <!-- Шкала времени -->
                                            <div class="conf-step__seances-timeline-scale">
                                                @php
                                                    $startTime = strtotime('10:00');
                                                    $endTime = strtotime('22:00');
                                                    $currentTime = $startTime;
                                                    $timelineWidth = 720; // Ширина таймлайна в пикселях
                                                    $totalMinutes = ($endTime - $startTime) / 60; // Всего минут
                                                    $minuteToPixelRatio = $timelineWidth / $totalMinutes; // Соотношение минут к пикселям
                                                @endphp

                                                @while ($currentTime <= $endTime)
                                                                        <div class="conf-step__seances-timeline-mark"
                                                                            style="left: {{ (($currentTime - $startTime) / 60) * $minuteToPixelRatio }}px;">
                                                                            {{ date('H:i', $currentTime) }}
                                                                        </div>
                                                                        @php
                                                                            $currentTime += 60 * 60; // Метки каждые 60 минут
                                                                        @endphp
                                                @endwhile
                                            </div>

                                            <!-- Таймлайн сеансов -->
                                            <div class="conf-step__seances-timeline" data-hall-id="{{ $hall->id }}">
                                                @foreach ($hall->seancesMovies()->whereDate('start_time', today())->orderBy('start_time')->get() as $seance)
                                                    <div class="conf-step__seances-movie"
                                                        style="width: {{ $seance->movie->duration / 60 * $minuteToPixelRatio }}px; 
                                                               left: {{ ($seance->start_time_minutes - 600) * $minuteToPixelRatio }}px; 
                                                               background-color: rgb({{ rand(133, 255) }}, {{ rand(200, 255) }}, {{ rand(133, 255) }});">
                                                        <p class="conf-step__seances-movie-title">{{ $seance->movie->title }}</p>
                                                        <p class="conf-step__seances-movie-start">{{ $seance->start_time_formatted }}</p>
                                                        <p class="conf-step__seances-movie-end">{{ $seance->end_time_formatted }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                    @endforeach
                </div>

                <!-- Кнопки управления -->
                <fieldset class="conf-step__buttons text-center">
                    <button class="conf-step__button conf-step__button-regular"
                        id="cancel-seances-button">Отмена</button>
                    <button class="conf-step__button conf-step__button-accent"
                        id="save-seances-button">Сохранить</button>
                </fieldset>
            </div>
        </section>
        <!-- Открыть продажи -->
        <section class="conf-step">
            <header class="conf-step__header conf-step__header_opened">
                <h2 class="conf-step__title">Открыть продажи</h2>
            </header>
            <div class="conf-step__wrapper">
                <p class="conf-step__paragraph">Выберите зал для открытия продажи билетов:</p>
                <ul class="conf-step__selectors-box">
                    @foreach ($halls as $hall)
                        <li>
                            <input type="radio" class="conf-step__radio" name="hall_id" value="{{ $hall->id }}"
                                data-hall-id="{{ $hall->id }}" data-is-active="{{ $hall->is_active }}">
                            <span class="conf-step__selector">{{ $hall->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="conf-step__wrapper text-center">
                <p class="conf-step__paragraph">Всё готово, теперь можно:</p>

                <!-- Кнопка с динамическим текстом -->
                <button id="toggleSalesButton" class="conf-step__button conf-step__button-accent" {{ $hallsExist ? '' : 'disabled' }}>
                    {{ $hallsExist ? ($isActive ? 'Приостановить продажу билетов' : 'Открыть продажу билетов') : 'Нет залов' }}
                </button>
            </div>
        </section>
        <!-- Выход из администраторской -->
        <section class="conf-step">
            <header class="conf-step__header conf-step__header_opened">
                <h2 class="conf-step__title">Выход из администраторской</h2>
            </header>
            <div class="conf-step__wrapper text-center">
                <!-- Кнопка выхода -->
                <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                    @csrf <!-- Добавляем CSRF токен -->
                    <button type="submit" class="conf-step__button conf-step__button-regular">Выход</button>
                </form>
            </div>
        </section>
    </main>
    @include('admin.partials.edit_movie_modal')
    @include('admin.partials.create_hall_modal')
    @include('admin.partials.create_movie_modal')
    <script src="{{ asset('assets/admin/js/accordeon.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hall_config.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hall.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hall_pricing.js') }}"></script>
    <script src="{{ asset('assets/admin/js/seances.js') }}"></script>
    <script src="{{ asset('assets/admin/js/movies.js') }}"></script>
    <script src="{{ asset('assets/admin/js/toggleSales.js') }}"></script>
</body>

</html>