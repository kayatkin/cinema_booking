<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('seances_movies', function (Blueprint $table) {
            $table->date('date')->nullable()->after('hall_id'); // Добавляем возможность принимать NULL
        });
    }

    public function down()
    {
        Schema::table('seances_movies', function (Blueprint $table) {
            $table->dropColumn('date'); // Удаляем при откате миграции
        });
    }
};