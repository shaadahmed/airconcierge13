<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `tot_reporting_methods` from live DB (schema only).
 */
class CreateTotReportingMethodsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tot_reporting_methods')) {
            return;
        }

        Schema::create('tot_reporting_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reporting_method', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tot_reporting_methods');
    }
}
