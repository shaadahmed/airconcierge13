<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_additional_fee` from live DB (schema only).
 */
class CreatePropertyAdditionalFeeTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_additional_fee')) {
            return;
        }

        Schema::create('property_additional_fee', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->enum('additional_fee_type', ['community_fee', 'resort_fee', 'daily_use_fee'])->nullable()->default('community_fee');
            $table->decimal('additional_fee', 11, 2)->nullable();
            $table->enum('additional_fee_currency_type', ['doller', 'percentage'])->nullable()->default('percentage');
            $table->enum('additional_fee_application', ['daily', 'per_reservation'])->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_additional_fee');
    }
}
