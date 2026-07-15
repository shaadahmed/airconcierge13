<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_visits_log` from live DB (schema only).
 */
class CreatePropertyVisitsLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_visits_log')) {
            return;
        }

        Schema::create('property_visits_log', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->integer('visitor_use_id')->nullable();
            $table->string('visitor_name', 255)->nullable();
            $table->date('visit_date')->nullable();
            $table->integer('ovservation_id')->nullable();
            $table->string('action_ids', 255)->nullable();
            $table->boolean('owner_need_response')->nullable()->default(0);
            $table->boolean('email_sent')->nullable()->default(0);
            $table->dateTime('datecreated')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_visits_log');
    }
}
