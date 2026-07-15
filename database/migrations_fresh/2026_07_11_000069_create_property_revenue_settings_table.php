<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_revenue_settings` from live DB (schema only).
 */
class CreatePropertyRevenueSettingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_revenue_settings')) {
            return;
        }

        Schema::create('property_revenue_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('property_id');
            $table->integer('min_nightly_rate')->nullable();
            $table->integer('min_nights_per_booking')->nullable();
            $table->integer('owner_max_stay')->nullable();
            $table->integer('booking_availability')->nullable();
            $table->longText('notes')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_revenue_settings');
    }
}
