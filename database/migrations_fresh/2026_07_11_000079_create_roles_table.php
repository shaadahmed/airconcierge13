<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `roles` from live DB (schema only).
 */
class CreateRolesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('roles')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('display_name', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->boolean('deleted')->nullable()->default(0);
            $table->unique('name', 'roles_name_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
