<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000073_create_regions_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('regions')) {
            return;
        }

        Schema::create('regions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('region_name', 255)->nullable();
            $table->string('shortcode', 50)->nullable();
            $table->string('color', 100)->nullable();
            $table->boolean('deleted')->nullable()->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
