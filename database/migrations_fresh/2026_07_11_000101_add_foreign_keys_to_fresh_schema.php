<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Fresh-baseline foreign keys (applied after all create migrations).
 */
class AddForeignKeysToFreshSchema extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_alerts') && Schema::hasTable('bookings')) {
            Schema::table('booking_alerts', function (Blueprint $table) {
                $table->foreign('booking_id', 'booking_alerts_booking_id_foreign')->references('id')->on('bookings');
            });
        }

        if (Schema::hasTable('management_fee_rules') && Schema::hasTable('properties')) {
            Schema::table('management_fee_rules', function (Blueprint $table) {
                $table->foreign('property_id', 'management_fee_rules_property_id_foreign')->references('id')->on('properties');
            });
        }

        if (Schema::hasTable('month_closing_logs') && Schema::hasTable('users')) {
            Schema::table('month_closing_logs', function (Blueprint $table) {
                $table->foreign('closed_by', 'month_closing_logs_closed_by_foreign')->references('id')->on('users');
            });
        }

        if (Schema::hasTable('outbound_email_attachments') && Schema::hasTable('outbound_email_logs')) {
            Schema::table('outbound_email_attachments', function (Blueprint $table) {
                $table->foreign('outbound_email_log_id', 'fk_outbound_email_attachments_log_id')->references('id')->on('outbound_email_logs')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('owner_block_abandonments') && Schema::hasTable('users')) {
            Schema::table('owner_block_abandonments', function (Blueprint $table) {
                $table->foreign('owner_id', 'fk_oba_owner_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            });
        }

        if (Schema::hasTable('owner_block_abandonments') && Schema::hasTable('properties')) {
            Schema::table('owner_block_abandonments', function (Blueprint $table) {
                $table->foreign('property_id', 'fk_oba_property_id')->references('id')->on('properties')->onDelete('cascade')->onUpdate('cascade');
            });
        }

        if (Schema::hasTable('owner_block_changes') && Schema::hasTable('bookings')) {
            Schema::table('owner_block_changes', function (Blueprint $table) {
                $table->foreign('booking_id', 'owner_block_changes_booking_id_foreign')->references('id')->on('bookings');
            });
        }

        if (Schema::hasTable('owner_block_changes') && Schema::hasTable('properties')) {
            Schema::table('owner_block_changes', function (Blueprint $table) {
                $table->foreign('property_id', 'owner_block_changes_property_id_foreign')->references('id')->on('properties');
            });
        }

        if (Schema::hasTable('property_audit_photos') && Schema::hasTable('property_audit_reports')) {
            Schema::table('property_audit_photos', function (Blueprint $table) {
                $table->foreign('audit_id', 'property_audit_photos_audit_id_foreign')->references('id')->on('property_audit_reports');
            });
        }

        if (Schema::hasTable('property_online_listings') && Schema::hasTable('platforms')) {
            Schema::table('property_online_listings', function (Blueprint $table) {
                $table->foreign('platform_id', 'property_online_listings_platform_id_foreign')->references('id')->on('platforms');
            });
        }

        if (Schema::hasTable('property_online_listings') && Schema::hasTable('properties')) {
            Schema::table('property_online_listings', function (Blueprint $table) {
                $table->foreign('property_id', 'property_online_listings_property_id_foreign')->references('id')->on('properties');
            });
        }

    }

    public function down()
    {
        if (Schema::hasTable('booking_alerts')) {
            Schema::table('booking_alerts', function (Blueprint $table) {
                $table->dropForeign('booking_alerts_booking_id_foreign');
            });
        }

        if (Schema::hasTable('management_fee_rules')) {
            Schema::table('management_fee_rules', function (Blueprint $table) {
                $table->dropForeign('management_fee_rules_property_id_foreign');
            });
        }

        if (Schema::hasTable('month_closing_logs')) {
            Schema::table('month_closing_logs', function (Blueprint $table) {
                $table->dropForeign('month_closing_logs_closed_by_foreign');
            });
        }

        if (Schema::hasTable('outbound_email_attachments')) {
            Schema::table('outbound_email_attachments', function (Blueprint $table) {
                $table->dropForeign('fk_outbound_email_attachments_log_id');
            });
        }

        if (Schema::hasTable('owner_block_abandonments')) {
            Schema::table('owner_block_abandonments', function (Blueprint $table) {
                $table->dropForeign('fk_oba_owner_id');
            });
        }

        if (Schema::hasTable('owner_block_abandonments')) {
            Schema::table('owner_block_abandonments', function (Blueprint $table) {
                $table->dropForeign('fk_oba_property_id');
            });
        }

        if (Schema::hasTable('owner_block_changes')) {
            Schema::table('owner_block_changes', function (Blueprint $table) {
                $table->dropForeign('owner_block_changes_booking_id_foreign');
            });
        }

        if (Schema::hasTable('owner_block_changes')) {
            Schema::table('owner_block_changes', function (Blueprint $table) {
                $table->dropForeign('owner_block_changes_property_id_foreign');
            });
        }

        if (Schema::hasTable('property_audit_photos')) {
            Schema::table('property_audit_photos', function (Blueprint $table) {
                $table->dropForeign('property_audit_photos_audit_id_foreign');
            });
        }

        if (Schema::hasTable('property_online_listings')) {
            Schema::table('property_online_listings', function (Blueprint $table) {
                $table->dropForeign('property_online_listings_platform_id_foreign');
            });
        }

        if (Schema::hasTable('property_online_listings')) {
            Schema::table('property_online_listings', function (Blueprint $table) {
                $table->dropForeign('property_online_listings_property_id_foreign');
            });
        }

    }
}
