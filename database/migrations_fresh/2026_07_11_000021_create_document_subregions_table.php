<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `document_subregions` from live DB (schema only).
 */
class CreateDocumentSubregionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('document_subregions')) {
            return;
        }

        Schema::create('document_subregions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('document_id');
            $table->integer('subregion_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_subregions');
    }
}
