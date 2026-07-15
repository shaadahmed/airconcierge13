<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `user_table_states` from live DB (schema only).
 */
class CreateUserTableStatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_table_states')) {
            return;
        }

        Schema::create('user_table_states', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->nullable();
            $table->integer('table_id')->nullable();
            $table->text('columns')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_table_states');
    }
}
