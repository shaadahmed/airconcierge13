<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `tbl_mailsend` from live DB (schema only).
 */
class CreateTblMailsendTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tbl_mailsend')) {
            return;
        }

        Schema::create('tbl_mailsend', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('owner_id')->nullable();
            $table->integer('properties_id')->nullable();
            $table->integer('document_id')->nullable();
            $table->integer('template_id')->nullable();
            $table->string('documentname', 256)->nullable();
            $table->string('files_url', 555)->nullable();
            $table->string('signing_url', 444)->nullable();
            $table->string('details_url', 455)->nullable();
            $table->string('signature_id', 555)->nullable();
            $table->integer('is_opened')->nullable();
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('update_date')->nullable();
            $table->unique('id', 'id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_mailsend');
    }
}
