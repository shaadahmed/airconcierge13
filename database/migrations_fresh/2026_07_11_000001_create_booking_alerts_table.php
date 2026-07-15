<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `booking_alerts` from live DB (schema only).
 */
class CreateBookingAlertsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_alerts')) {
            return;
        }

        Schema::create('booking_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id');
            $table->enum('alert_type', ['SECURITY_DEPOSIT']);
            $table->boolean('is_triggered')->default(0);
            $table->dateTime('trigger_time');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
            $table->index('booking_id', 'booking_alerts_booking_id_foreign');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_alerts');
    }
}
