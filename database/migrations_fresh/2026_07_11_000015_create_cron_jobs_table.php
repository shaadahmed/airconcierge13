<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `cron_jobs` from live DB (schema only).
 */
class CreateCronJobsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cron_jobs')) {
            return;
        }

        Schema::create('cron_jobs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title', 50);
            $table->string('subtitle', 100);
            $table->string('icon_path', 200);
            $table->text('description');
            $table->boolean('is_active')->default(1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cron_jobs');
    }
}
