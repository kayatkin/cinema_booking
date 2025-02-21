<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('seances_movies', function (Blueprint $table) {
            $table->id(); // ID сеанса
            $table->foreignId('movie_id')->constrained()->onDelete('cascade'); // Связь с фильмом
            $table->foreignId('hall_id')->constrained()->onDelete('cascade'); // Связь с залом
            $table->time('start_time'); // Время начала сеанса
            $table->time('end_time'); // Время окончания сеанса
            $table->timestamps(); // Даты создания и обновления
        });
    }

    public function down()
    {
        Schema::dropIfExists('seances_movies');
    }
};