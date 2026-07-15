<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `booking_payments` from live DB (schema only).
 */
class CreateBookingPaymentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_payments')) {
            return;
        }

        Schema::create('booking_payments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->integer('booking_id')->nullable();
            $table->integer('payment_type_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->date('expense_incurred_date')->nullable();
            $table->decimal('price', 11, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->boolean('payment_status')->default(1);
            $table->string('vendor_name', 50)->nullable();
            $table->integer('vendor_id');
            $table->string('receipt', 255)->nullable();
            $table->dateTime('compressed_at')->nullable();
            $table->text('payment_notes')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('is_chain_unlinked')->default(0);
            $table->integer('property_payment_type')->nullable();
            $table->integer('purchaser_id')->nullable();
            $table->index('property_id', 'property_payments_payment_id');
            $table->index('expense_incurred_date', 'expense_incurred_date');
            $table->index('payment_date', 'payment_date');
            $table->index('parent_id', 'parent_id');
            $table->index('compressed_at', 'booking_payments_compressed_at_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_payments');
    }
}
