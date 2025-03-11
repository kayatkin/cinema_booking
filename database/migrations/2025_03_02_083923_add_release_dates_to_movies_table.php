<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->date('start_of_release')->nullable()->after('poster_path'); // Добавляем после poster_path
            $table->date('end_of_release')->nullable()->after('start_of_release'); // Добавляем после start_of_release
        });
    }

    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['start_of_release', 'end_of_release']); // Удаляем при откате миграции
        });
    }
};