<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subregions under regions (core identity + tax/stay settings).
 *
 * Booking limits and permits live in related tables (1–1 or 1–many — TBD in models).
 * Legacy boolean `deleted` → SoftDeletes (ADR-021).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subregions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->decimal('transient_occupancy_tax', 11, 2)->nullable();
            $table->smallInteger('short_stay_len')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subregions');
    }
};
