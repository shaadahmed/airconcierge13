<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `database_entry_logs` from live DB (schema only).
 */
class CreateDatabaseEntryLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('database_entry_logs')) {
            return;
        }

        Schema::create('database_entry_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('record_type', ['BOOKING_PAYMENT', 'PROPERTY_PAYMENT', 'BOOKING', 'BOOKING_OWNERBLOCK', 'OWNER', 'GUEST', 'PROPERTY', 'PROPERTY_AVAILABILITY', 'PROP_AUDIT_REPORT', '']);
            $table->unsignedInteger('record_id');
            $table->enum('action_type', ['CREATE', 'EDIT', 'DELETE']);
            $table->unsignedInteger('admin_id')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('database_entry_logs');
    }
}
