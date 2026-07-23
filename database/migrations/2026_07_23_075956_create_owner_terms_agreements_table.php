<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000048_create_owner_terms_agreements_table.php
 *
 * Timestamps use nullable defaults (no legacy 0000-00-00).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owner_terms_agreements')) {
            return;
        }

        Schema::create('owner_terms_agreements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->boolean('agreed_terms')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_terms_agreements');
    }
};
