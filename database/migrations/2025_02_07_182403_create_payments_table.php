<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // ID платежа
            $table->foreignId('seances_movie_id')->constrained()->onDelete('cascade'); // Связь с моделью SeancesMovie
            $table->decimal('total_price', 8, 2)->default(0); // Общая стоимость билетов
            $table->string('status')->default('pending'); // Статус платежа (например, 'pending', 'completed', 'cancelled')
            $table->timestamps(); // Даты создания и обновления
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};