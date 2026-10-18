<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Geography lookup: cities under states.
 *
 * Aligned with legacy `cities` (already had timestamps/deleted_at) + admin city form fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->string('code', 15)->nullable();
            $table->integer('zip')->nullable()->index();
            $table->string('contact_code', 10)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
