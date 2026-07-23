<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000094_create_user_owners_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_owners')) {
            return;
        }

        Schema::create('user_owners', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_owners');
    }
};
