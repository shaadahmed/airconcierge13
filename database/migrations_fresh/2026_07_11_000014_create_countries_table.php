<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `countries` from live DB (schema only).
 */
class CreateCountriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('countries')) {
            return;
        }

        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 255);
            $table->string('name', 255);
            $table->double('lat', 8, 2);
            $table->double('lng', 8, 2);
        });
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
