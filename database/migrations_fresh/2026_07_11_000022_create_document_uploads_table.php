<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `document_uploads` from live DB (schema only).
 */
class CreateDocumentUploadsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('document_uploads')) {
            return;
        }

        Schema::create('document_uploads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->string('ownerspecific', 255)->nullable();
            $table->string('document', 255)->nullable();
            $table->string('roletitle', 255)->nullable();
            $table->string('nextstepinstruction', 255)->nullable();
            $table->integer('type')->nullable();
            $table->string('signid', 255)->nullable();
            $table->string('zohoactionid', 255)->nullable();
            $table->string('dropbox_form_name', 255)->nullable();
            $table->integer('allregion')->nullable();
            $table->integer('allsubregion')->nullable();
            $table->dateTime('createdate')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_uploads');
    }
}
