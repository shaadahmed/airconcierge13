<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `subregions` from live DB (schema only).
 */
class CreateSubregionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('subregions')) {
            return;
        }

        Schema::create('subregions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('region_id')->nullable();
            $table->string('subregion_name', 255)->nullable();
            $table->boolean('airbnb_tot_region')->nullable()->default(0);
            $table->decimal('transient_occupancy_tax', 11, 2)->nullable();
            $table->enum('limit_type', ['NIGHTS', 'RESERVATIONS'])->nullable();
            $table->unsignedSmallInteger('limit_value')->nullable();
            $table->string('limit_filter', 50)->nullable();
            $table->smallInteger('short_stay_len')->nullable();
            $table->integer('business_license_account')->default(0);
            $table->string('permit_no', 30)->nullable();
            $table->date('permit_issue_date')->nullable();
            $table->date('permit_expiry_date')->nullable();
            $table->integer('permit_length')->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subregions');
    }
}
