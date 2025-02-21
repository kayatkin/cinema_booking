<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Добавьте дополнительные поля, если необходимо
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Удалите добавленные поля, если необходимо
        });
    }
}