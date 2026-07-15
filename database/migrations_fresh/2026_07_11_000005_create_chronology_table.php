<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `chronology` from live DB (schema only).
 */
class CreateChronologyTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('chronology')) {
            return;
        }

        Schema::create('chronology', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->date('startdate');
            $table->integer('allregion')->nullable();
            $table->integer('allsubregion')->nullable();
            $table->integer('chronologyoption')->nullable();
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('update_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chronology');
    }
}
