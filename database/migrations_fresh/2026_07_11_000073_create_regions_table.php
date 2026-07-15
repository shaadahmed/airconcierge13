<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `regions` from live DB (schema only).
 */
class CreateRegionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('regions')) {
            return;
        }

        Schema::create('regions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('region_name', 255)->nullable();
            $table->string('shortcode', 50)->nullable();
            $table->string('color', 100)->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('regions');
    }
}
