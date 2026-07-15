<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `guests` from live DB (schema only).
 */
class CreateGuestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('guests')) {
            return;
        }

        Schema::create('guests', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('guest_name', 255)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->boolean('blacklisted')->nullable()->default(0);
            $table->text('notes')->nullable();
            $table->boolean('corporate_travel_booking_agent')->nullable()->default(0);
            $table->string('corporate_travel_booking_agent_name_email', 255)->nullable();
            $table->string('corporate_travel_booking_agent_first_name', 255)->nullable();
            $table->string('corporate_travel_booking_agent_last_name', 255)->nullable();
            $table->string('corporate_business_name', 255)->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('guests');
    }
}
