<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `outbound_email_logs` from live DB (schema only).
 */
class CreateOutboundEmailLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('outbound_email_logs')) {
            return;
        }

        Schema::create('outbound_email_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
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
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['status', 'created_at'], 'idx_outbound_email_logs_status_created');
        });
    }

    public function down()
    {
        Schema::dropIfExists('outbound_email_logs');
    }
}
