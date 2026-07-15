<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `hostaway_access_tokens` from live DB (schema only).
 */
class CreateHostawayAccessTokensTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('hostaway_access_tokens')) {
            return;
        }

        Schema::create('hostaway_access_tokens', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('access_token', 1024);
            $table->string('token_type', 255);
            $table->date('expiry');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostaway_access_tokens');
    }
}
