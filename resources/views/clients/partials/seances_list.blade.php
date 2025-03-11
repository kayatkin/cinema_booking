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
        @php
            $groupedSeances = $seances->groupBy('hall.name');
        @endphp
        @foreach($groupedSeances as $hallName => $hallSeances)
            <div class="movie-seances__hall">
                <h3 class="movie-seances__hall-title">{{ $hallName }}</h3>
                <ul class="movie-seances__list">
                    @foreach($hallSeances as $seance)
                        <li class="movie-seances__time-block">
                            <a class="movie-seances__time"
                                href="{{ route('clients.select_seat', ['seance_id' => $seance->id]) }}">
                                {{ \Carbon\Carbon::parse($seance->start_time)->format('H:i') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </section>
@endforeach
