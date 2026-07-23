<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000082_create_subregions_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subregions')) {
            return;
        }

        Schema::create('subregions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable()->index();
            $table->string('subregion_name', 255)->nullable();
            $table->boolean('airbnb_tot_region')->nullable()->default(false);
            $table->decimal('transient_occupancy_tax', 11, 2)->nullable();
            $table->string('limit_type', 20)->nullable();
            $table->unsignedSmallInteger('limit_value')->nullable();
            $table->string('limit_filter', 50)->nullable();
            $table->smallInteger('short_stay_len')->nullable();
            $table->integer('business_license_account')->default(0);
            $table->string('permit_no', 30)->nullable();
            $table->date('permit_issue_date')->nullable();
            $table->date('permit_expiry_date')->nullable();
            $table->integer('permit_length')->nullable();
            $table->boolean('deleted')->nullable()->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subregions');
    }
};
