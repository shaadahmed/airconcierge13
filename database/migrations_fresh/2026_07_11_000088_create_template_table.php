<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `template` from live DB (schema only).
 */
class CreateTemplateTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('template')) {
            return;
        }

        Schema::create('template', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->string('templatesubject', 255)->nullable();
            $table->string('acknowledgementsubject', 255)->nullable();
            $table->text('acknowledgement')->nullable();
            $table->text('acknowledgementdescription')->nullable();
            $table->text('description')->nullable();
            $table->integer('allregion')->nullable();
            $table->integer('allsubregion')->nullable();
            $table->dateTime('createdate')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('template');
    }
}
