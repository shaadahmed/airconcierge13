<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `manager_regions` from live DB (schema only).
 */
class CreateManagerRegionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('manager_regions')) {
            return;
        }

        Schema::create('manager_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('manager_id')->nullable();
            $table->integer('region_id')->nullable();
            $table->enum('subregion', ['ALL', 'SOME'])->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manager_regions');
    }
}
