<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `management_types` from live DB (schema only).
 */
class CreateManagementTypesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('management_types')) {
            return;
        }

        Schema::create('management_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('management_types');
    }
}
