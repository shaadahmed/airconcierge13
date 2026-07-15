<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `subscribers` from live DB (schema only).
 */
class CreateSubscribersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('subscribers')) {
            return;
        }

        Schema::create('subscribers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('email', 255)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('address_2', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('zip', 255)->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->string('lease', 255)->nullable();
            $table->string('pets', 10)->nullable();
            $table->date('date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscribers');
    }
}
