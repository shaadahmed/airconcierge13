<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapted from database/migrations_fresh/2026_07_11_000031_create_hostaway_reservation_logs_table.php
 *
 * booking_id is nullable without a foreign key until the bookings domain lands (ADR-011).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hostaway_reservation_logs')) {
            return;
        }

        Schema::create('hostaway_reservation_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reservation_id');
            $table->integer('booking_id')->nullable();
            $table->string('booking_code', 50)->nullable();
            $table->string('guest_name', 255)->nullable();
            $table->integer('status');
            $table->string('log_type', 20)->nullable();
            $table->string('comments', 1000)->nullable();
            $table->text('hostaway_response')->nullable();
            $table->text('difference_array')->nullable();
            $table->timestamps();

            $table->index(['reservation_id', 'log_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostaway_reservation_logs');
    }
};
