<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `outbound_email_attachments` from live DB (schema only).
 */
class CreateOutboundEmailAttachmentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('outbound_email_attachments')) {
            return;
        }

        Schema::create('outbound_email_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('outbound_email_log_id');
            $table->string('original_filename', 255);
            $table->string('mime_type', 100)->nullable();
            $table->enum('storage_type', ['disk', 'database'])->default('disk');
            $table->string('disk_path', 500)->nullable();
            $table->binary('content')->nullable();
            $table->unsignedInteger('size_bytes')->default(0);
            $table->string('content_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('outbound_email_log_id', 'idx_outbound_email_attachments_log_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('outbound_email_attachments');
    }
}
