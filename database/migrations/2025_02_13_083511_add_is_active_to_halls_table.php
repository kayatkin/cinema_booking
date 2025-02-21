<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('seats_per_row');
        });
    }

    public function down()
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
