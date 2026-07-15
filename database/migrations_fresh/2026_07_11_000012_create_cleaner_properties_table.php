<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `cleaner_properties` from live DB (schema only).
 */
class CreateCleanerPropertiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cleaner_properties')) {
            return;
        }

        Schema::create('cleaner_properties', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('cleaner_id')->nullable();
            $table->integer('property_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cleaner_properties');
    }
}
