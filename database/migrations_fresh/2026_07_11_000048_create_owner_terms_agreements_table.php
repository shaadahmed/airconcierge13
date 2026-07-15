<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `owner_terms_agreements` from live DB (schema only).
 */
class CreateOwnerTermsAgreementsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('owner_terms_agreements')) {
            return;
        }

        Schema::create('owner_terms_agreements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->boolean('agreed_terms');
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    public function down()
    {
        Schema::dropIfExists('owner_terms_agreements');
    }
}
