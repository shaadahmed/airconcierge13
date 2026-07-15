<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `chronology_mail` from live DB (schema only).
 */
class CreateChronologyMailTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('chronology_mail')) {
            return;
        }

        Schema::create('chronology_mail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('chronology_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('chronologyorder')->nullable();
            $table->integer('owner_id')->nullable();
            $table->dateTime('created_on')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('update_on')->nullable();
            $table->string('is_opened', 444)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chronology_mail');
    }
}
