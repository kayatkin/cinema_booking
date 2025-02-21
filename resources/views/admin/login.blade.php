<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Авторизация | ИдёмВКино</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/CSS/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/CSS/styles.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900&amp;subset=cyrillic,cyrillic-ext,latin-ext" rel="stylesheet">
</head>
<body>
    <header class="page-header">
        <h1 class="page-header__title">Идём<span>в</span>кино</h1>
        <span class="page-header__subtitle">Администраторская</span>
    </header>

    <main>
        <section class="login">
            <header class="login__header">
                <h2 class="login__title">Авторизация</h2>
            </header>
            <div class="login__wrapper">
                <form class="login__form" action="{{ route('admin.login.submit') }}" method="POST" accept-charset="utf-8">
                    @csrf
                    <label class="login__label" for="email">
                        E-mail
                        <input class="login__input" type="email" placeholder="example@domain.xyz" name="email" required>
                    </label>
                    <label class="login__label" for="password">
                        Пароль
                        <input class="login__input" type="password" placeholder="" name="password" required>
                    </label>
                    <div class="text-center">
                        <button type="submit" class="login__button">Авторизоваться</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
</body>
</html>