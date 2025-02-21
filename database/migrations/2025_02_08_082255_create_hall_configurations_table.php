<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('hall_configurations', function (Blueprint $table) {
            $table->id(); // ID конфигурации
            $table->foreignId('hall_id')->constrained()->onDelete('cascade'); // Связь с залом
            $table->integer('global_seat_number')->default(1); // Глобальный номер места в пределах зала
            $table->string('seat_type')->default('standart'); // Тип места (standart, vip, disabled)
            $table->unique(['hall_id', 'global_seat_number']); // Уникальность глобального номера места в пределах зала
            $table->timestamps(); // Даты создания и обновления
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_configurations');
    }
};