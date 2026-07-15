<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `password_expiries` from live DB (schema only).
 */
class CreatePasswordExpiriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('password_expiries')) {
            return;
        }

        Schema::create('password_expiries', function (Blueprint $table) {
            $table->integer('user_id');
            $table->string('token', 255);
            $table->dateTime('token_timestamp');
            $table->date('notification_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_expiries');
    }
}
