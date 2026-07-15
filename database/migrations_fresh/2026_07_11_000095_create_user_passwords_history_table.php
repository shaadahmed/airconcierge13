<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `user_passwords_history` from live DB (schema only).
 */
class CreateUserPasswordsHistoryTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_passwords_history')) {
            return;
        }

        Schema::create('user_passwords_history', function (Blueprint $table) {
            $table->integer('user_id');
            $table->string('password', 60);
            $table->timestamp('date_time')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_passwords_history');
    }
}
