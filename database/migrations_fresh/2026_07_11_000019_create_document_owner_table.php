<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `document_owner` from live DB (schema only).
 */
class CreateDocumentOwnerTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('document_owner')) {
            return;
        }

        Schema::create('document_owner', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('document_id');
            $table->integer('owner_id');
            $table->integer('properties_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_owner');
    }
}
