<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000056_create_properties_table.php
 *
 * Core property columns for foundation / chronology / bookings. Satellite
 * tables (cleaners, audits, metrics) land with Phase 2.5 as needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('properties')) {
            return;
        }

        Schema::create('properties', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable()->index();
            $table->unsignedInteger('subregion_id')->nullable()->index();
            $table->integer('hostaway_listing_id')->nullable()->index();
            $table->string('property_title', 255)->nullable();
            $table->string('limit_type', 20)->nullable();
            $table->smallInteger('limit_value')->nullable();
            $table->string('email_title', 255)->nullable();
            $table->string('unit_number', 10)->nullable();
            $table->string('street_address', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('modified_date')->nullable();
            $table->unsignedInteger('last_modified_by')->nullable();
            $table->boolean('status')->nullable()->default(true)->index();
            $table->date('inactive_date')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('contract_end_reason', 255)->nullable();
            $table->integer('management_type_id')->default(1);
            $table->decimal('ac_management_fee', 11, 2)->nullable()->default(0);
            $table->integer('bathrooms')->nullable()->default(0);
            $table->integer('bedrooms')->nullable()->default(0);
            $table->decimal('owner_monthly_costs', 11, 2)->nullable();
            $table->string('property_code', 255)->nullable();
            $table->string('supportemail', 256)->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('property_image_url', 255)->nullable();
            $table->boolean('deleted')->nullable()->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
