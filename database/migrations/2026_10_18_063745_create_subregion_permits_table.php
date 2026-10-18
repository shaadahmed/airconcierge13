<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business-license / permit records for a subregion.
 *
 * No unique on `subregion_id` so cardinality can be 1–1 or 1–many (decide in Eloquent).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subregion_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subregion_id')->constrained()->cascadeOnDelete();
            $table->integer('business_license_account')->default(0);
            $table->string('permit_no', 30)->nullable();
            $table->date('permit_issue_date')->nullable();
            $table->date('permit_expiry_date')->nullable();
            $table->integer('permit_length')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subregion_permits');
    }
};
