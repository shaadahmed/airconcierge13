<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `incoming_payment_types` from live DB (schema only).
 */
class CreateIncomingPaymentTypesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('incoming_payment_types')) {
            return;
        }

        Schema::create('incoming_payment_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title', 255)->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('incoming_payment_types');
    }
}
