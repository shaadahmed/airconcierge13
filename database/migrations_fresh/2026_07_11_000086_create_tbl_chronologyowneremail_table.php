<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `tbl_chronologyowneremail` from live DB (schema only).
 */
class CreateTblChronologyowneremailTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tbl_chronologyowneremail')) {
            return;
        }

        Schema::create('tbl_chronologyowneremail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('chronology_id')->nullable();
            $table->integer('owner_id')->nullable();
            $table->string('owner_email', 255)->nullable();
            $table->integer('status')->nullable();
            $table->dateTime('created_date')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_date')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_chronologyowneremail');
    }
}
