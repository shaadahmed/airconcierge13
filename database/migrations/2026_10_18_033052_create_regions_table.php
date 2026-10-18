<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operational regions (admin Regions & Subregions).
 *
 * Modernized from legacy `regions`: boolean `deleted` → SoftDeletes (ADR-021).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('color', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
