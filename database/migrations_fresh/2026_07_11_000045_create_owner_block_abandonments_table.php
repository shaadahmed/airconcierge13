<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `owner_block_abandonments` from live DB (schema only).
 */
class CreateOwnerBlockAbandonmentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('owner_block_abandonments')) {
            return;
        }

        Schema::create('owner_block_abandonments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id');
            $table->unsignedInteger('owner_id');
            $table->date('block_start_date');
            $table->date('block_end_date');
            $table->smallInteger('nights');
            $table->decimal('estimated_revenue', 10, 2)->default(0.00);
            $table->string('data_tier', 10)->nullable();
            $table->dateTime('abandoned_at');
            $table->enum('outcome', ['open', 'booked', 're_blocked', 'expired'])->default('open');
            $table->boolean('subsequently_blocked')->default(0);
            $table->boolean('partial_overlap')->default(0);
            $table->text('recovered_booking_ids')->nullable();
            $table->decimal('recovered_mgmt_fee', 10, 2)->default(0.00);
            $table->smallInteger('view_count')->default(1);
            $table->dateTime('cron_last_checked')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->index('property_id', 'idx_oba_property_id');
            $table->index('owner_id', 'idx_oba_owner_id');
            $table->index('outcome', 'idx_oba_outcome');
            $table->index('abandoned_at', 'idx_oba_abandoned_at');
            $table->index(['block_start_date', 'block_end_date'], 'idx_oba_block_dates');
        });
    }

    public function down()
    {
        Schema::dropIfExists('owner_block_abandonments');
    }
}
