<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('seances_movies', function (Blueprint $table) {
            // ID сеанса (автоинкрементное поле типа BIGSERIAL в PostgreSQL)
            $table->id();

            // Внешний ключ на таблицу movies
            $table->foreignId('movie_id')
                  ->constrained() // Создает внешний ключ на таблицу movies (по умолчанию 'movies' + '_id')
                  ->onDelete('cascade'); // При удалении записи из movies удаляются связанные записи в seances_movies

            // Внешний ключ на таблицу halls
            $table->foreignId('hall_id')
                  ->constrained() // Создает внешний ключ на таблицу halls (по умолчанию 'halls' + '_id')
                  ->onDelete('cascade'); // При удалении записи из halls удаляются связанные записи в seances_movies

            // Время начала сеанса
            $table->time('start_time');

            // Время окончания сеанса
            $table->time('end_time');

            // Timestamps для created_at и updated_at
            $table->timestamps();
        });
    }

    public function down()
    {
        // Удаление таблицы seances_movies
        Schema::dropIfExists('seances_movies');
    }
};
