<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `payment_receipts` from live DB (schema only).
 */
class CreatePaymentReceiptsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payment_receipts')) {
            return;
        }

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id');
            $table->integer('property_payment_id')->nullable();
            $table->integer('booking_id')->nullable();
            $table->integer('booking_payment_id')->nullable();
            $table->string('receipt', 255);
            $table->dateTime('compressed_at')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('deleted_at')->nullable();
            $table->index('compressed_at', 'payment_receipts_compressed_at_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_receipts');
    }
}
