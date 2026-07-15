<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `users_login_logs` from live DB (schema only).
 */
class CreateUsersLoginLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users_login_logs')) {
            return;
        }

        Schema::create('users_login_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->dateTime('datetime');
            $table->integer('user_id');
            $table->string('username', 255);
            $table->integer('owner_id')->nullable();
            $table->string('ipaddress', 32);
            $table->boolean('success')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_login_logs');
    }
}
