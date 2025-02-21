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
        <h1 class="page-header__title" onclick="window.location.href='{{ route('clients.seances') }}'"
            style="cursor: pointer;">
            Идём<span>в</span>кино
        </h1>
    </header>

    <main>
        <section class="ticket">
            <header class="ticket__check">
                <h2 class="ticket__check-title">Вы выбрали билет:</h2>
            </header>

            <div class="ticket__info-wrapper">
                <p class="ticket__info">На фильм: <span
                        class="ticket__details ticket__title">{{ $payment->seancesMovie->movie->title }}</span></p>
                <p class="ticket__info">Места: <span
                        class="ticket__details ticket__chairs">{{ $payment->ticket->seat_list }}</span></p>
                <p class="ticket__info">В зале: <span
                        class="ticket__details ticket__hall">{{ $payment->seancesMovie->hall->name }}</span></p>
                <p class="ticket__info">Начало сеанса: <span
                        class="ticket__details ticket__start">{{ $payment->seancesMovie->start_time->format('H:i') }}</span>
                </p>
                <p class="ticket__info">Стоимость: <span
                        class="ticket__details ticket__cost">{{ $payment->total_price }}</span> рублей</p>
                <button class="acceptin-button"
                    onclick="location.href='{{ route("clients.tickets.show", $payment->ticket->id) }}'">Получить код
                    бронирования</button>
                <p class="ticket__hint">После оплаты билет будет доступен в этом окне, а также придёт вам на почту.
                    Покажите QR-код нашему контроллёру у входа в зал.</p>
                <p class="ticket__hint">Приятного просмотра!</p>
            </div>
        </section>
    </main>
</body>

</html>