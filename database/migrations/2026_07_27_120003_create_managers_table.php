<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `managers` from live DB (schema only).
 */
class CreateManagersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('managers')) {
            return;
        }

        Schema::create('managers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->boolean('regional_manager')->nullable()->default(0);
            $table->tinyInteger('is_active')->nullable()->default(1);
            $table->text('compensation_structure')->nullable();
            $table->string('home_address', 100)->nullable();
            $table->integer('employment_status')->nullable();
            $table->integer('salary_type')->nullable();
            $table->boolean('deleted')->nullable()->default(0);
            $table->enum('region', ['ALL', 'SOME'])->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('managers');
    }
}
