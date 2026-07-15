<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `hellosigndetails` from live DB (schema only).
 */
class CreateHellosigndetailsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('hellosigndetails')) {
            return;
        }

        Schema::create('hellosigndetails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('files_url', 255)->nullable();
            $table->string('signing_url', 255)->nullable();
            $table->string('details_url', 255)->nullable();
            $table->string('signature_id', 255)->nullable();
            $table->integer('ownerid_id')->nullable();
            $table->string('ownerid_email', 255)->nullable();
            $table->string('filename', 255)->nullable();
            $table->integer('chronology_id')->nullable();
            $table->integer('chronologyorder_id')->nullable();
            $table->integer('is_opened')->default(0);
            $table->string('zoho_request_name', 255)->nullable();
            $table->string('zoho_document_id', 255)->nullable();
            $table->string('zoho_zsdocumentid', 255)->nullable();
            $table->string('zoho_template_ids', 255)->nullable();
            $table->string('zoho_request_id', 255)->nullable();
            $table->integer('zoho_sign_status')->nullable()->default(0);
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('update_date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hellosigndetails');
    }
}
