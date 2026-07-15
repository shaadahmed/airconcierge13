<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `insurances` from live DB (schema only).
 */
class CreateInsurancesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('insurances')) {
            return;
        }

        Schema::create('insurances', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('carrier_name', 80);
            $table->string('insured_name', 80);
            $table->enum('additional_insurance', ['0', '1', '2']);
            $table->boolean('signed_waiver')->default(0);
            $table->string('policy_no', 30);
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('insurance_copy', 100)->nullable();
            $table->enum('insurance_type', ['CLEANER', 'PROPERTY']);
            $table->integer('insurance_type_id');
            $table->timestamp('deleted_at')->nullable();
            $table->index('insurance_type_id', 'insurance_type_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('insurances');
    }
}
