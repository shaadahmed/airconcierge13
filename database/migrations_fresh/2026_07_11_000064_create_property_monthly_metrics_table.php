<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_monthly_metrics` from live DB (schema only).
 */
class CreatePropertyMonthlyMetricsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_monthly_metrics')) {
            return;
        }

        Schema::create('property_monthly_metrics', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id');
            $table->integer('month');
            $table->decimal('avg_adr', 8, 2)->nullable();
            $table->decimal('occupancy_rate', 8, 2)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_monthly_metrics');
    }
}
