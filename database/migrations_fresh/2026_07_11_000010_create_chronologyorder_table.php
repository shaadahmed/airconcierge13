<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `chronologyorder` from live DB (schema only).
 */
class CreateChronologyorderTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('chronologyorder')) {
            return;
        }

        Schema::create('chronologyorder', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('chronology_id')->nullable();
            $table->integer('day')->nullable();
            $table->string('time', 444)->nullable();
            $table->string('minute', 444)->nullable();
            $table->string('previousaction', 255)->nullable();
            $table->integer('document_id')->nullable();
            $table->integer('template_id')->nullable();
            $table->string('document_name', 255)->nullable();
            $table->string('template_name', 255)->nullable();
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('update_date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chronologyorder');
    }
}
