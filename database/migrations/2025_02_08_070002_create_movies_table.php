<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id(); // ID фильма
            $table->string('title'); // Название фильма
            $table->integer('duration')->default(0); // Длительность фильма (в минутах)
            $table->text('synopsis')->nullable(); // Описание фильма
            $table->string('origin')->nullable(); // Страна производства
            $table->string('poster_path')->nullable(); // Путь к постеру
            $table->timestamps(); // Даты создания и обновления
        });
    }

    public function down()
    {
        Schema::dropIfExists('movies');
    }
};