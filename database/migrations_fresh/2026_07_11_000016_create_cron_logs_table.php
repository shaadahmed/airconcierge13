<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `cron_logs` from live DB (schema only).
 */
class CreateCronLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cron_logs')) {
            return;
        }

        Schema::create('cron_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('cron_name', 50)->nullable();
            $table->date('email_date')->nullable();
            $table->integer('property_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cron_logs');
    }
}
