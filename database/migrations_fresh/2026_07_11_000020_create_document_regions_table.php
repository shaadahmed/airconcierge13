<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `document_regions` from live DB (schema only).
 */
class CreateDocumentRegionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('document_regions')) {
            return;
        }

        Schema::create('document_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('document_id');
            $table->integer('region_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_regions');
    }
}
