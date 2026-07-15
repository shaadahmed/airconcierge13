<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `month_closing_logs` from live DB (schema only).
 */
class CreateMonthClosingLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('month_closing_logs')) {
            return;
        }

        Schema::create('month_closing_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('closing_date');
            $table->string('month', 2);
            $table->string('year', 4);
            $table->date('payout_date');
            $table->integer('payout_disbursed');
            $table->unsignedInteger('closed_by');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
            $table->index('closed_by', 'month_closing_logs_closed_by_foreign');
        });
    }

    public function down()
    {
        Schema::dropIfExists('month_closing_logs');
    }
}
