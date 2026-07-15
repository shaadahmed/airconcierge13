<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_online_listings` from live DB (schema only).
 */
class CreatePropertyOnlineListingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_online_listings')) {
            return;
        }

        Schema::create('property_online_listings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url', 255);
            $table->string('username', 30);
            $table->integer('property_id');
            $table->integer('platform_id');
            $table->integer('platform_property_id')->nullable();
            $table->enum('status', ['0', '1', '2'])->default('0');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index('property_id', 'property_online_listings_property_id_index');
            $table->index('platform_id', 'property_online_listings_platform_id_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_online_listings');
    }
}
