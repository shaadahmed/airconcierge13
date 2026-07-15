<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `guests_bookings` from live DB (schema only).
 */
class CreateGuestsBookingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('guests_bookings')) {
            return;
        }

        Schema::create('guests_bookings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('guest_id')->nullable();
            $table->integer('booking_id')->nullable();
            $table->index('guest_id', 'guest_id');
            $table->index('booking_id', 'booking_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('guests_bookings');
    }
}
