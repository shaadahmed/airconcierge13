<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `dynamic_content` from live DB (schema only).
 */
class CreateDynamicContentTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('dynamic_content')) {
            return;
        }

        Schema::create('dynamic_content', function (Blueprint $table) {
            $table->increments('id');
            $table->string('page_id', 50);
            $table->text('content');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dynamic_content');
    }
}
