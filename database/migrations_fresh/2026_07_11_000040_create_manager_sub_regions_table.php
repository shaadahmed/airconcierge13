<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `manager_sub_regions` from live DB (schema only).
 */
class CreateManagerSubRegionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('manager_sub_regions')) {
            return;
        }

        Schema::create('manager_sub_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('manager_regions_id');
            $table->integer('sub_region_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manager_sub_regions');
    }
}
