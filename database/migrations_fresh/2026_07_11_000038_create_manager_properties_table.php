<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `manager_properties` from live DB (schema only).
 */
class CreateManagerPropertiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('manager_properties')) {
            return;
        }

        Schema::create('manager_properties', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('manager_id')->nullable();
            $table->integer('property_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manager_properties');
    }
}
