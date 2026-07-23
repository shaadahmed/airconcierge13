<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2.4 / 2.5 supporting tables for reports, documents admin, imports.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_monthly_metrics')) {
            Schema::create('property_monthly_metrics', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('property_id')->nullable()->index();
                $table->unsignedSmallInteger('year')->nullable();
                $table->unsignedTinyInteger('month')->nullable();
                $table->decimal('revenue', 12, 2)->nullable()->default(0);
                $table->decimal('occupancy', 8, 2)->nullable()->default(0);
                $table->integer('nights_booked')->nullable()->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('region_monthly_metrics')) {
            Schema::create('region_monthly_metrics', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('region_id')->nullable()->index();
                $table->unsignedSmallInteger('year')->nullable();
                $table->unsignedTinyInteger('month')->nullable();
                $table->decimal('revenue', 12, 2)->nullable()->default(0);
                $table->decimal('occupancy', 8, 2)->nullable()->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('imported_emails')) {
            Schema::create('imported_emails', function (Blueprint $table) {
                $table->increments('id');
                $table->string('source', 100)->nullable();
                $table->string('subject', 500)->nullable();
                $table->text('body')->nullable();
                $table->string('from_email', 255)->nullable();
                $table->string('status', 50)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tot_reporting_methods')) {
            Schema::create('tot_reporting_methods', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->boolean('deleted')->nullable()->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tot_reporting_methods');
        Schema::dropIfExists('imported_emails');
        Schema::dropIfExists('region_monthly_metrics');
        Schema::dropIfExists('property_monthly_metrics');
    }
};
