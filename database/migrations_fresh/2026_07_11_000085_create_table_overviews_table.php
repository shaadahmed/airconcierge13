<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `table_overviews` from live DB (schema only).
 */
class CreateTableOverviewsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('table_overviews')) {
            return;
        }

        Schema::create('table_overviews', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('overview_name', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('table_overviews');
    }
}
