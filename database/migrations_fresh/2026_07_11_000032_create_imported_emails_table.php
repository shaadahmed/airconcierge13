<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `imported_emails` from live DB (schema only).
 */
class CreateImportedEmailsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('imported_emails')) {
            return;
        }

        Schema::create('imported_emails', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->string('email_number', 255)->nullable();
            $table->text('subject')->nullable();
            $table->string('from', 255)->nullable();
            $table->dateTime('date')->nullable();
            $table->text('message_id')->nullable();
            $table->integer('platform_id')->default(1);
            $table->string('size', 255)->nullable();
            $table->string('uid', 255)->nullable();
            $table->string('msgno', 255)->nullable();
            $table->string('recent', 10)->nullable();
            $table->string('flagged', 10)->nullable();
            $table->string('answered', 10)->nullable();
            $table->string('deleted', 10)->nullable();
            $table->string('seen', 10)->nullable();
            $table->string('draft', 10)->nullable();
            $table->dateTime('udate')->nullable();
            $table->longText('email_message_body')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('dateCreated')->nullable();
            $table->boolean('processed')->nullable()->default(0);
            $table->dateTime('processed_date')->nullable();
            $table->boolean('booking')->nullable()->default(0);
            $table->integer('booking_id')->nullable()->default(0);
            $table->boolean('property_not_found')->nullable()->default(0);
            $table->boolean('booking_calcel_flag')->nullable()->default(0);
            $table->index('from', 'airbnb_emails_from_index');
            $table->index('date', 'airbnb_emails_date_index');
            $table->index('msgno', 'msgno');
            $table->index('subject', 'airbnb_emails_subject_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('imported_emails');
    }
}
