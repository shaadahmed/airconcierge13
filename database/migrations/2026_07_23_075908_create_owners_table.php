<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000049_create_owners_table.php
 *
 * ADR-009 / ADR-012: legacy `owners.status` is intentionally omitted — account
 * enablement is `users.active` only. Active property access is computed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owners')) {
            return;
        }

        Schema::create('owners', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable()->index();
            $table->string('full_name', 255)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('owner_phone', 50)->nullable();
            $table->string('owner_email', 100)->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->text('owner_payout_information')->nullable();
            $table->boolean('w9_on_file')->nullable()->default(false);
            $table->integer('emailstatus')->nullable();
            $table->boolean('deleted')->nullable()->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
