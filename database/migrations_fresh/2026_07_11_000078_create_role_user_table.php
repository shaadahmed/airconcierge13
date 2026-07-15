<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `role_user` from live DB (schema only).
 */
class CreateRoleUserTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('role_user')) {
            return;
        }

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('role_id');
            $table->primary(['user_id', 'role_id']);
            $table->index('role_id', 'role_user_role_id_foreign');
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}
