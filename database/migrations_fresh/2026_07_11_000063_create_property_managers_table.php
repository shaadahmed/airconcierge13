<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_managers` from live DB (schema only).
 */
class CreatePropertyManagersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_managers')) {
            return;
        }

        Schema::create('property_managers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->boolean('deleted')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_managers');
    }
}
