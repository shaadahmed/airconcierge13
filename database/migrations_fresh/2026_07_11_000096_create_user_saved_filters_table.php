<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `user_saved_filters` from live DB (schema only).
 */
class CreateUserSavedFiltersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_saved_filters')) {
            return;
        }

        Schema::create('user_saved_filters', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('module', ['dashboard'])->nullable();
            $table->integer('user_id')->nullable();
            $table->text('filters')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_saved_filters');
    }
}
