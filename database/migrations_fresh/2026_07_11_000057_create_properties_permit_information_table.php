<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `properties_permit_information` from live DB (schema only).
 */
class CreatePropertiesPermitInformationTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('properties_permit_information')) {
            return;
        }

        Schema::create('properties_permit_information', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->date('initial_permit_date')->nullable();
            $table->string('permit_validty_period', 255)->nullable();
            $table->date('permit_renewal_date')->nullable();
            $table->string('permit_number', 30);
            $table->string('permit_copy', 100)->nullable();
            $table->tinyInteger('extended_home_share')->default(0);
            $table->boolean('deleted')->nullable()->default(0);
            $table->string('tot_license_number', 120)->nullable();
            $table->string('tot_license_date', 120)->nullable();
            $table->string('business_license_number', 120)->nullable();
            $table->string('business_license_date', 120)->nullable();
            $table->string('business_license_expiry', 120)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('properties_permit_information');
    }
}
