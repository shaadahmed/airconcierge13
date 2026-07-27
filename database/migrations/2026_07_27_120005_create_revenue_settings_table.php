<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `revenue_settings` from live DB (schema only).
 */
class CreateRevenueSettingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('revenue_settings')) {
            return;
        }

        Schema::create('revenue_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('property_id');
            $table->integer('dynamic_pricing')->nullable();
            $table->decimal('base_rate', 8, 2)->nullable();
            $table->decimal('min_rate', 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('revenue_settings');
    }
}
