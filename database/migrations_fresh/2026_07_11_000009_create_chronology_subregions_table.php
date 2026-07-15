<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `chronology_subregions` from live DB (schema only).
 */
class CreateChronologySubregionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('chronology_subregions')) {
            return;
        }

        Schema::create('chronology_subregions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('chronology_id');
            $table->integer('subregion_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chronology_subregions');
    }
}
