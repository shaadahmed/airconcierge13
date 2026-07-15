<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_audit_reports` from live DB (schema only).
 */
class CreatePropertyAuditReportsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_audit_reports')) {
            return;
        }

        Schema::create('property_audit_reports', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('visitor_id')->nullable();
            $table->string('visitor_name', 20)->nullable();
            $table->date('visit_date')->nullable();
            $table->integer('property_id')->nullable();
            $table->text('visit_purpose')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('guest_owner_notes')->nullable();
            $table->text('ac_relay_owner')->nullable();
            $table->text('ac_relay_cleaning')->nullable();
            $table->text('ac_relay_guest')->nullable();
            $table->boolean('lockbox_key')->default(0);
            $table->text('lockbox_key_comment')->nullable();
            $table->boolean('backup_battery')->default(0);
            $table->text('backup_battery_comment')->nullable();
            $table->boolean('garbage_can')->default(0);
            $table->text('garbage_can_comment')->nullable();
            $table->boolean('owner_mail_disposed')->default(0);
            $table->text('owner_mail_disposed_comment')->nullable();
            $table->boolean('sinks_and_drains')->default(0);
            $table->text('sinks_and_drains_comment')->nullable();
            $table->boolean('exterior_walls')->default(0);
            $table->text('exterior_walls_comment')->nullable();
            $table->boolean('exterior_doors_windows')->default(0);
            $table->text('exterior_doors_windows_comment')->nullable();
            $table->boolean('lights')->default(0);
            $table->text('lights_comment')->nullable();
            $table->boolean('landscape')->default(0);
            $table->text('landscape_comment')->nullable();
            $table->boolean('damage_signs')->default(0);
            $table->text('damage_signs_comment')->nullable();
            $table->text('anything_else')->nullable();
            $table->tinyInteger('checked_smoke_detector')->default(0);
            $table->text('checked_smoke_detector_comment')->nullable();
            $table->integer('replaced_smoke_detector')->default(0);
            $table->text('replaced_smoke_detector_comment')->nullable();
            $table->boolean('hourly_work')->nullable();
            $table->integer('no_of_hours')->nullable();
            $table->integer('materials_cost')->nullable();
            $table->text('notes')->nullable();
            $table->date('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_audit_reports');
    }
}
