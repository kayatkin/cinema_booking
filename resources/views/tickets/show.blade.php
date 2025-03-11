<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Электронный билет</title>
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
        <section class="ticket">
            <header class="tichet__check">
                <h2 class="ticket__check-title">Электронный билет</h2>
            </header>

            <div class="ticket__info-wrapper">
                <p class="ticket__info">На фильм: <span
                        class="ticket__details ticket__title">{{ $ticket->payment->seancesMovie->movie->title }}</span>
                </p>
                <p class="ticket__info">Места: <span
                        class="ticket__details ticket__chairs">{{ $ticket->seat_list }}</span></p>
                <p class="ticket__info">В зале: <span
                        class="ticket__details ticket__hall">{{ $ticket->payment->seancesMovie->hall->name }}</span></p>
                <p class="ticket__info">Дата сеанса: <span
                        class="ticket__details ticket__start">{{ \Carbon\Carbon::parse($ticket->payment->seancesMovie->start_time)->format('d.m.Y') }}</span>
                </p>
                <p class="ticket__info">Начало сеанса: <span
                        class="ticket__details ticket__start">{{ $ticket->payment->seancesMovie->start_time->format('H:i') }}</span>
                </p>
                <img class="ticket__info-qr" src="{{ asset('storage/' . $ticket->qr_code_path) }}" alt="QR-код">
                <p class="ticket__hint">Покажите QR-код нашему контроллеру для подтверждения бронирования.</p>
                <p class="ticket__hint">Приятного просмотра!</p>
            </div>
        </section>
    </main>
</body>

</html>