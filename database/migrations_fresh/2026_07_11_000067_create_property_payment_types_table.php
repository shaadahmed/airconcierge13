<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_payment_types` from live DB (schema only).
 */
class CreatePropertyPaymentTypesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_payment_types')) {
            return;
        }

        Schema::create('property_payment_types', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->float('convenience_fee', 8, 2)->default(0.00);
            $table->float('management_markup', 8, 2)->default(0.00);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_payment_types');
    }
}
