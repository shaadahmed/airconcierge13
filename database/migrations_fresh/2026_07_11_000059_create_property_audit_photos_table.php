<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_audit_photos` from live DB (schema only).
 */
class CreatePropertyAuditPhotosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_audit_photos')) {
            return;
        }

        Schema::create('property_audit_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('audit_id');
            $table->string('file_name', 60);
            $table->dateTime('compressed_at')->nullable();
            $table->index('audit_id', 'property_audit_photos_audit_id_index');
            $table->index('compressed_at', 'property_audit_photos_compressed_at_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_audit_photos');
    }
}
