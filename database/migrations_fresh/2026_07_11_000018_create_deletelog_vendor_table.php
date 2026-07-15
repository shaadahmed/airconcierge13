<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `deletelog_vendor` from live DB (schema only).
 */
class CreateDeletelogVendorTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('deletelog_vendor')) {
            return;
        }

        Schema::create('deletelog_vendor', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vendor_name', 255);
            $table->integer('vendor_id')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('status')->nullable();
            $table->integer('isDelete')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deletelog_vendor');
    }
}
