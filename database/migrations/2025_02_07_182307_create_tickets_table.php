<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->string('seat_list'); // Список выбранных мест
            $table->string('unique_code')->unique(); // Уникальный код бронирования
            $table->string('status')->default('active'); // Статус билета
            $table->string('qr_code_path')->nullable(); // Путь к QR-коду
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};