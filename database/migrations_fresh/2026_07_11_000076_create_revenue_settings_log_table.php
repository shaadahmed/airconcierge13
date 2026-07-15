<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `revenue_settings_log` from live DB (schema only).
 */
class CreateRevenueSettingsLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('revenue_settings_log')) {
            return;
        }

        Schema::create('revenue_settings_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('settings_id');
            $table->decimal('value', 8, 2);
            $table->string('field', 255);
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
        });
    }

    public function down()
    {
        Schema::dropIfExists('revenue_settings_log');
    }
}
