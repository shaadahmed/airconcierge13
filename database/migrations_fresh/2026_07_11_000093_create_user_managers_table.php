<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `user_managers` from live DB (schema only).
 */
class CreateUserManagersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_managers')) {
            return;
        }

        Schema::create('user_managers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('manager_id')->nullable();
            $table->integer('user_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_managers');
    }
}
