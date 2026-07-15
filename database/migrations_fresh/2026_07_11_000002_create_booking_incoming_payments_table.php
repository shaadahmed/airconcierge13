<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `booking_incoming_payments` from live DB (schema only).
 */
class CreateBookingIncomingPaymentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_incoming_payments')) {
            return;
        }

        Schema::create('booking_incoming_payments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->integer('booking_id')->nullable();
            $table->integer('payment_type_id')->nullable();
            $table->decimal('price', 11, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->boolean('payment_status')->nullable()->default(0);
            $table->string('receipt', 255)->nullable();
            $table->text('payment_notes')->nullable();
            $table->index('property_id', 'property_payments_payment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_incoming_payments');
    }
}
