<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `owner_block_changes` from live DB (schema only).
 */
class CreateOwnerBlockChangesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('owner_block_changes')) {
            return;
        }

        Schema::create('owner_block_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('property_id');
            $table->integer('booking_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('booking_notes')->nullable();
            $table->boolean('exit_cleaning');
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->index('property_id', 'owner_block_changes_property_id_index');
            $table->index('booking_id', 'owner_block_changes_booking_id_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('owner_block_changes');
    }
}
