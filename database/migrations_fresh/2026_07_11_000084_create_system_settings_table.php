<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `system_settings` from live DB (schema only).
 */
class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('system_settings')) {
            return;
        }

        Schema::create('system_settings', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->string('setting_key', 255);
            $table->string('setting_value', 1000)->nullable();
            $table->string('type', 100)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
            $table->unique('setting_key', 'setting_key');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
}
