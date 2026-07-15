<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `management_fee_rules` from live DB (schema only).
 */
class CreateManagementFeeRulesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('management_fee_rules')) {
            return;
        }

        Schema::create('management_fee_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('property_id');
            $table->decimal('lower_bound', 8, 2);
            $table->decimal('upper_bound', 8, 2)->nullable();
            $table->enum('rule_type', ['NIGHTLY_RATE', 'BOOKING_LENGTH']);
            $table->enum('rule_condition', ['EQUALS', 'GREATER', 'RANGE', 'SMALLER']);
            $table->decimal('management_fee', 8, 2);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('deleted_at')->nullable();
            $table->index('property_id', 'management_fee_rules_property_id_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('management_fee_rules');
    }
}
