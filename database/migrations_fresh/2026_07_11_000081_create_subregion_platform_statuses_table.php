<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `subregion_platform_statuses` from live DB (schema only).
 */
class CreateSubregionPlatformStatusesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('subregion_platform_statuses')) {
            return;
        }

        Schema::create('subregion_platform_statuses', function (Blueprint $table) {
            $table->integer('subregion_id');
            $table->integer('platform_id');
            $table->boolean('status');
            $table->index('subregion_id', 'subregion_platform_statuses_subregion_id_index');
            $table->index('platform_id', 'subregion_platform_statuses_platform_id_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subregion_platform_statuses');
    }
}
