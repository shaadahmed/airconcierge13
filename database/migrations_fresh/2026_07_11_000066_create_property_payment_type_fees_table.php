<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_payment_type_fees` from live DB (schema only).
 */
class CreatePropertyPaymentTypeFeesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_payment_type_fees')) {
            return;
        }

        Schema::create('property_payment_type_fees', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id');
            $table->integer('property_payment_type_id');
            $table->boolean('use_default')->default(1);
            $table->float('convenience_fee', 8, 2)->nullable();
            $table->float('management_markup', 8, 2)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('deleted_at')->nullable();
            $table->index('property_id', 'property_id');
            $table->index('property_payment_type_id', 'property_payment_type_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_payment_type_fees');
    }
}
