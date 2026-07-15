<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `template_subregions` from live DB (schema only).
 */
class CreateTemplateSubregionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('template_subregions')) {
            return;
        }

        Schema::create('template_subregions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id');
            $table->integer('subregion_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_subregions');
    }
}
