<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `dropbox_form_list` from live DB (schema only).
 */
class CreateDropboxFormListTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('dropbox_form_list')) {
            return;
        }

        Schema::create('dropbox_form_list', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 180)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('guid', 60)->nullable();
            $table->timestamp('created_dtm')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('update_dtm')->nullable();
            $table->integer('is_deleted')->nullable()->default(0);
            $table->integer('status')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dropbox_form_list');
    }
}
