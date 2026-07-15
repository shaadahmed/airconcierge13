<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_email_titles` from live DB (schema only).
 */
class CreatePropertyEmailTitlesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_email_titles')) {
            return;
        }

        Schema::create('property_email_titles', function (Blueprint $table) {
            $table->unsignedInteger('property_id');
            $table->string('email_title', 255);
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->index('property_id', 'property_email_titles_property_id_foreign');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_email_titles');
    }
}
