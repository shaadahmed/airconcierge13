<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `tot_authorizations` from live DB (schema only).
 */
class CreateTotAuthorizationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tot_authorizations')) {
            return;
        }

        Schema::create('tot_authorizations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('permit_id');
            $table->boolean('tot_authorized')->default(1);
            $table->boolean('poa')->default(0);
            $table->string('poa_file', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('end_reason', 300)->nullable();
            $table->index('permit_id', 'tot_authorizations_permit_id_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tot_authorizations');
    }
}
