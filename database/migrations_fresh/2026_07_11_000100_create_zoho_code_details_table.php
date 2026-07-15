<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `zoho_code_details` from live DB (schema only).
 */
class CreateZohoCodeDetailsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('zoho_code_details')) {
            return;
        }

        Schema::create('zoho_code_details', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('zoho_code', 255)->nullable();
            $table->string('zoho_access_token', 255)->nullable();
            $table->string('zoho_refresh_token', 255)->nullable();
            $table->timestamp('created_dtm')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('update_dtm')->nullable();
            $table->integer('is_deleted')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoho_code_details');
    }
}
