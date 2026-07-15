<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `cities` from live DB (schema only).
 */
class CreateCitiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cities')) {
            return;
        }

        Schema::create('cities', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('state_id');
            $table->string('name', 100);
            $table->string('code', 15)->nullable();
            $table->integer('zip')->nullable();
            $table->string('contact_code', 10)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index('state_id', 'state_id');
            $table->index('contact_code', 'contact_code');
            $table->index('zip', 'zip');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
}
