<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('halls', function (Blueprint $table) {
            $table->id(); // ID зала
            $table->string('name')->unique(); // Название зала
            $table->integer('rows')->default(0); // Количество рядов
            $table->integer('seats_per_row')->default(0); // Количество мест в ряду
            $table->timestamps(); // Даты создания и обновления
        });
    }

    public function down()
    {
        Schema::dropIfExists('halls');
    }
};
