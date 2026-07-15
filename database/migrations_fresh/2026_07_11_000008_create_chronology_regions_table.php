<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `chronology_regions` from live DB (schema only).
 */
class CreateChronologyRegionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('chronology_regions')) {
            return;
        }

        Schema::create('chronology_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('chronology_id');
            $table->integer('region_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chronology_regions');
    }
}
