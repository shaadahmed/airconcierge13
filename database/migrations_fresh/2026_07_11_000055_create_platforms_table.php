<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `platforms` from live DB (schema only).
 */
class CreatePlatformsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('platforms')) {
            return;
        }

        Schema::create('platforms', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('platform_name', 255)->nullable();
            $table->string('hostaway_channel_id', 100)->nullable();
            $table->boolean('include_in_charts')->nullable()->default(1);
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('platforms');
    }
}
