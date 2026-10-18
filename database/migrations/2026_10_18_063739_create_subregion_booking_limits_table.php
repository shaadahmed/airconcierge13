<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Booking-limit rules for a subregion.
 *
 * No unique on `subregion_id` so cardinality can be 1–1 or 1–many (decide in Eloquent).
 * Frontend maps limited_bookings / bookings_limit / nights_limit → limit_type + limit_value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subregion_booking_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subregion_id')->constrained()->cascadeOnDelete();
            $table->enum('limit_type', ['NIGHTS', 'RESERVATIONS'])->nullable();
            $table->unsignedSmallInteger('limit_value')->nullable();
            $table->string('limit_filter', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subregion_booking_limits');
    }
};
