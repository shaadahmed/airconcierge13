<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `user_owners` from live DB (schema only).
 */
class CreateUserOwnersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_owners')) {
            return;
        }

        Schema::create('user_owners', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('owner_id')->nullable();
            $table->integer('user_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_owners');
    }
}
