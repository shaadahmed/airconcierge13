<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `template_regions` from live DB (schema only).
 */
class CreateTemplateRegionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('template_regions')) {
            return;
        }

        Schema::create('template_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id');
            $table->integer('region_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_regions');
    }
}
