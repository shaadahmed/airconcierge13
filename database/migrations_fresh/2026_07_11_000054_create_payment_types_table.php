<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `payment_types` from live DB (schema only).
 */
class CreatePaymentTypesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payment_types')) {
            return;
        }

        Schema::create('payment_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title', 255)->nullable();
            $table->string('booking_field', 255)->nullable();
            $table->string('category', 255)->nullable();
            $table->string('subcategory', 255)->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_types');
    }
}
