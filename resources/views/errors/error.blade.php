<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка</title>
</head>

<body>
    <h1>Ошибка {{ $errorCode ?? 500 }}</h1>
    <p>{{ $errorMessage ?? 'Произошла непредвиденная ошибка.' }}</p>
</body>

</html>