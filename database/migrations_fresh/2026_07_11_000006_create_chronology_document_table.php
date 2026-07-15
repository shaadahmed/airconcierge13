<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `chronology_document` from live DB (schema only).
 */
class CreateChronologyDocumentTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('chronology_document')) {
            return;
        }

        Schema::create('chronology_document', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('chronology_id');
            $table->integer('document_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chronology_document');
    }
}
