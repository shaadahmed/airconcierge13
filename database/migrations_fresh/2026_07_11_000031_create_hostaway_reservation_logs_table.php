<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `hostaway_reservation_logs` from live DB (schema only).
 */
class CreateHostawayReservationLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('hostaway_reservation_logs')) {
            return;
        }

        Schema::create('hostaway_reservation_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('reservation_id');
            $table->integer('booking_id')->nullable();
            $table->string('booking_code', 50)->nullable();
            $table->string('guest_name', 255)->nullable();
            $table->integer('status');
            $table->enum('log_type', ['booking', 'review'])->nullable();
            $table->string('comments', 1000)->nullable();
            $table->text('hostaway_response')->nullable();
            $table->text('difference_array')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostaway_reservation_logs');
    }
}
