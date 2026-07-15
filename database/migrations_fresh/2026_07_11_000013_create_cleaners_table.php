<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `cleaners` from live DB (schema only).
 */
class CreateCleanersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cleaners')) {
            return;
        }

        Schema::create('cleaners', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('region_id')->nullable();
            $table->integer('subregion_id');
            $table->string('full_name', 255)->nullable();
            $table->string('company', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('hired_by', 100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('position', 100)->nullable();
            $table->float('standard_cleaning_fee', 8, 2)->nullable();
            $table->float('touch_up_cleaning_fee', 8, 2)->nullable();
            $table->float('cleaner_hourly_rate', 8, 2)->nullable();
            $table->tinyInteger('wnine')->default(1);
            $table->tinyInteger('booking_fee')->default(0);
            $table->unsignedSmallInteger('booking_fee_percentage')->nullable();
            $table->string('insurance_certificate', 255)->nullable();
            $table->date('issuance_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cleaners');
    }
}
