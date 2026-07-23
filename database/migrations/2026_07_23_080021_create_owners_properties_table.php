<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000051_create_owners_properties_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owners_properties')) {
            return;
        }

        Schema::create('owners_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id')->nullable()->index();
            $table->unsignedInteger('property_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owners_properties');
    }
};
