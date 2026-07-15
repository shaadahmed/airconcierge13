<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `resources` from live DB (schema only).
 */
class CreateResourcesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('resources')) {
            return;
        }

        Schema::create('resources', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('parent_id')->default(0);
            $table->string('name', 255);
            $table->string('path', 255);
            $table->string('description', 255)->default('Resource Description Goes Here...');
            $table->boolean('link')->default(1);
            $table->boolean('external_link')->nullable()->default(0);
            $table->integer('order')->default(0);
            $table->string('icon_class', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('deleted')->nullable()->default(0);
            $table->index('path', 'path');
        });
    }

    public function down()
    {
        Schema::dropIfExists('resources');
    }
}
