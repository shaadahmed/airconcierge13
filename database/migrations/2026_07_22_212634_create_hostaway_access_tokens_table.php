<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000030_create_hostaway_access_tokens_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hostaway_access_tokens')) {
            return;
        }

        Schema::create('hostaway_access_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('access_token', 1024);
            $table->string('token_type', 255);
            $table->date('expiry');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostaway_access_tokens');
    }
};
