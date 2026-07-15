<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `role_permissions` from live DB (schema only).
 */
class CreateRolePermissionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('role_permissions')) {
            return;
        }

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('role_id');
            $table->integer('resource_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
}
