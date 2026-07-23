<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2.2 Email / Chronology domain tables (adapted from migrations_fresh).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_uploads')) {
            Schema::create('document_uploads', function (Blueprint $table) {
                $table->increments('id');
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
                $table->timestamp('createdate')->useCurrent();
            });
        }

        if (! Schema::hasTable('document_regions')) {
            Schema::create('document_regions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('document_id');
                $table->unsignedInteger('region_id');
            });
        }

        if (! Schema::hasTable('document_subregions')) {
            Schema::create('document_subregions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('document_id');
                $table->unsignedInteger('subregion_id');
            });
        }

        if (! Schema::hasTable('document_owner')) {
            Schema::create('document_owner', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('document_id')->nullable();
                $table->unsignedInteger('owner_id')->nullable();
            });
        }

        if (! Schema::hasTable('template')) {
            Schema::create('template', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->string('templatesubject', 255)->nullable();
                $table->string('acknowledgementsubject', 255)->nullable();
                $table->text('acknowledgement')->nullable();
                $table->text('acknowledgementdescription')->nullable();
                $table->text('description')->nullable();
                $table->integer('allregion')->nullable();
                $table->integer('allsubregion')->nullable();
                $table->timestamp('createdate')->useCurrent();
            });
        }

        if (! Schema::hasTable('template_regions')) {
            Schema::create('template_regions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('template_id');
                $table->unsignedInteger('region_id');
            });
        }

        if (! Schema::hasTable('template_subregions')) {
            Schema::create('template_subregions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('template_id');
                $table->unsignedInteger('subregion_id');
            });
        }

        if (! Schema::hasTable('chronology')) {
            Schema::create('chronology', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->date('startdate');
                $table->integer('allregion')->nullable();
                $table->integer('allsubregion')->nullable();
                $table->integer('chronologyoption')->nullable();
                $table->timestamp('created_date')->useCurrent();
                $table->timestamp('update_date')->nullable();
            });
        }

        if (! Schema::hasTable('chronology_regions')) {
            Schema::create('chronology_regions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('chronology_id');
                $table->unsignedInteger('region_id');
            });
        }

        if (! Schema::hasTable('chronology_subregions')) {
            Schema::create('chronology_subregions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('chronology_id');
                $table->unsignedInteger('subregion_id');
            });
        }

        if (! Schema::hasTable('chronology_document')) {
            Schema::create('chronology_document', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('chronology_id');
                $table->unsignedInteger('document_id');
            });
        }

        if (! Schema::hasTable('chronologyorder')) {
            Schema::create('chronologyorder', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('chronology_id')->nullable()->index();
                $table->integer('day')->nullable();
                $table->string('time', 20)->nullable();
                $table->string('minute', 20)->nullable();
                $table->string('previousaction', 255)->nullable();
                $table->unsignedInteger('document_id')->nullable();
                $table->unsignedInteger('template_id')->nullable();
                $table->string('document_name', 255)->nullable();
                $table->string('template_name', 255)->nullable();
                $table->timestamp('created_date')->useCurrent();
                $table->timestamp('update_date')->nullable();
            });
        }

        if (! Schema::hasTable('chronology_mail')) {
            Schema::create('chronology_mail', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('chronology_id')->nullable()->index();
                $table->unsignedInteger('order_id')->nullable()->index();
                $table->integer('chronologyorder')->nullable();
                $table->unsignedInteger('owner_id')->nullable()->index();
                $table->timestamp('created_on')->useCurrent();
                $table->timestamp('update_on')->nullable();
                $table->string('is_opened', 20)->nullable();
            });
        }

        if (! Schema::hasTable('tbl_chronologyowneremail')) {
            Schema::create('tbl_chronologyowneremail', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('chronology_id')->nullable()->index();
                $table->unsignedInteger('owner_id')->nullable()->index();
                $table->string('owner_email', 255)->nullable();
                $table->integer('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tbl_mailsend')) {
            Schema::create('tbl_mailsend', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('owner_id')->nullable()->index();
                $table->unsignedInteger('properties_id')->nullable();
                $table->unsignedInteger('document_id')->nullable();
                $table->unsignedInteger('template_id')->nullable();
                $table->string('documentname', 256)->nullable();
                $table->string('files_url', 555)->nullable();
                $table->string('signing_url', 444)->nullable();
                $table->string('details_url', 455)->nullable();
                $table->string('signature_id', 555)->nullable();
                $table->integer('is_opened')->nullable();
                $table->timestamp('created_date')->useCurrent();
                $table->timestamp('update_date')->nullable();
            });
        }

        if (! Schema::hasTable('outbound_email_logs')) {
            Schema::create('outbound_email_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('status', 20)->default('pending');
                $table->string('subject', 500);
                $table->longText('body');
                $table->text('to_addresses');
                $table->text('cc_addresses')->nullable();
                $table->text('bcc_addresses')->nullable();
                $table->string('from_email', 255)->nullable();
                $table->string('from_name', 255)->nullable();
                $table->text('reply_to')->nullable();
                $table->text('error_message')->nullable();
                $table->unsignedTinyInteger('attempt_count')->default(1);
                $table->unsignedInteger('last_attempt_by')->nullable();
                $table->string('source', 100)->nullable();
                $table->text('metadata')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('outbound_email_attachments')) {
            Schema::create('outbound_email_attachments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('outbound_email_log_id')->index();
                $table->string('original_filename', 255);
                $table->string('mime_type', 100)->nullable();
                $table->string('storage_type', 20)->default('disk');
                $table->string('disk_path', 500)->nullable();
                $table->unsignedInteger('size_bytes')->default(0);
                $table->string('content_hash', 64)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hellosigndetails')) {
            Schema::create('hellosigndetails', function (Blueprint $table) {
                $table->increments('id');
                $table->string('files_url', 255)->nullable();
                $table->string('signing_url', 255)->nullable();
                $table->string('details_url', 255)->nullable();
                $table->string('signature_id', 255)->nullable();
                $table->unsignedInteger('ownerid_id')->nullable()->index();
                $table->string('ownerid_email', 255)->nullable();
                $table->string('filename', 255)->nullable();
                $table->unsignedInteger('chronology_id')->nullable()->index();
                $table->unsignedInteger('chronologyorder_id')->nullable();
                $table->integer('is_opened')->default(0);
                $table->string('zoho_request_name', 255)->nullable();
                $table->string('zoho_document_id', 255)->nullable();
                $table->string('zoho_zsdocumentid', 255)->nullable();
                $table->string('zoho_template_ids', 255)->nullable();
                $table->string('zoho_request_id', 255)->nullable()->index();
                $table->integer('zoho_sign_status')->nullable()->default(0);
                $table->timestamp('created_date')->useCurrent();
                $table->timestamp('update_date')->nullable();
            });
        }

        if (! Schema::hasTable('zoho_code_details')) {
            Schema::create('zoho_code_details', function (Blueprint $table) {
                $table->increments('id');
                $table->string('zoho_code', 255)->nullable();
                $table->text('zoho_access_token')->nullable();
                $table->text('zoho_refresh_token')->nullable();
                $table->timestamp('created_dtm')->nullable();
                $table->timestamp('update_dtm')->nullable();
                $table->integer('is_deleted')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('zoho_code_details');
        Schema::dropIfExists('hellosigndetails');
        Schema::dropIfExists('outbound_email_attachments');
        Schema::dropIfExists('outbound_email_logs');
        Schema::dropIfExists('tbl_mailsend');
        Schema::dropIfExists('tbl_chronologyowneremail');
        Schema::dropIfExists('chronology_mail');
        Schema::dropIfExists('chronologyorder');
        Schema::dropIfExists('chronology_document');
        Schema::dropIfExists('chronology_subregions');
        Schema::dropIfExists('chronology_regions');
        Schema::dropIfExists('chronology');
        Schema::dropIfExists('template_subregions');
        Schema::dropIfExists('template_regions');
        Schema::dropIfExists('template');
        Schema::dropIfExists('document_owner');
        Schema::dropIfExists('document_subregions');
        Schema::dropIfExists('document_regions');
        Schema::dropIfExists('document_uploads');
    }
};
