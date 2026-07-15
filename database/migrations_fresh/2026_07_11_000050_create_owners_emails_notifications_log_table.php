<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `owners_emails_notifications_log` from live DB (schema only).
 */
class CreateOwnersEmailsNotificationsLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('owners_emails_notifications_log')) {
            return;
        }

        Schema::create('owners_emails_notifications_log', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->integer('booking_id')->nullable();
            $table->integer('email_id')->nullable();
            $table->string('email_subject', 255)->nullable();
            $table->integer('owner_id')->nullable();
            $table->string('owner_first_name', 255)->nullable();
            $table->string('owner_last_name', 255)->nullable();
            $table->string('owner_email', 255)->nullable();
            $table->boolean('email_sent')->nullable()->default(0);
            $table->text('error_message')->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('last_updated')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('owners_emails_notifications_log');
    }
}
