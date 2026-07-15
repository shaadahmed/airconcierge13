<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `owner_blocks` from live DB (schema only).
 */
class CreateOwnerBlocksTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('owner_blocks')) {
            return;
        }

        Schema::create('owner_blocks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_booking');
            $table->boolean('exit_cleaning');
        });
    }

    public function down()
    {
        Schema::dropIfExists('owner_blocks');
    }
}
