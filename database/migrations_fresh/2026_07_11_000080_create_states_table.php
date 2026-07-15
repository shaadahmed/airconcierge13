<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `states` from live DB (schema only).
 */
class CreateStatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('states')) {
            return;
        }

        Schema::create('states', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->nullable();
            $table->string('code', 255);
            $table->string('name', 255);
            $table->double('lat', 8, 2);
            $table->double('lng', 8, 2);
            $table->tinyInteger('deleted');
            $table->index('country_id', 'country_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('states');
    }
}
