<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_visits_log_photos` from live DB (schema only).
 */
class CreatePropertyVisitsLogPhotosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_visits_log_photos')) {
            return;
        }

        Schema::create('property_visits_log_photos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_visit_id')->nullable();
            $table->string('photo', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_visits_log_photos');
    }
}
