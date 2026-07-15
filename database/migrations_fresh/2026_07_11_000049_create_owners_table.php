<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `owners` from live DB (schema only).
 */
class CreateOwnersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('owners')) {
            return;
        }

        Schema::create('owners', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('region_id')->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('owner_phone', 50)->nullable();
            $table->string('owner_email', 100)->nullable();
            $table->enum('payment_method', ['Airbnb Co Host', 'Direct Deposit', 'Paypal', 'Another owner is receiving under an above method', 'Credit Card (Co Host)', 'Direct Deposit (Co Host)'])->nullable();
            $table->text('owner_payout_information')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->boolean('w9_on_file')->nullable()->default(0);
            $table->integer('emailstatus')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('owners');
    }
}
