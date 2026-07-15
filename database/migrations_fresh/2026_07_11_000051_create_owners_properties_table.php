<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `owners_properties` from live DB (schema only).
 */
class CreateOwnersPropertiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('owners_properties')) {
            return;
        }

        Schema::create('owners_properties', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('owner_id')->nullable();
            $table->integer('property_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('owners_properties');
    }
}
