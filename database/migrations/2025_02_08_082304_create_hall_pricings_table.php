<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('hall_pricings', function (Blueprint $table) {
            $table->id(); // ID цены
            $table->foreignId('hall_id')->constrained()->onDelete('cascade'); // Связь с залом
            $table->string('seat_type')->default('standart'); // Тип места (standart, vip, disabled)
            $table->decimal('price', 8, 2)->default(0); // Цена за место
            $table->unique(['hall_id', 'seat_type']); // Уникальность цены для типа места в пределах зала
            $table->timestamps(); // Даты создания и обновления
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_pricings');
    }
};