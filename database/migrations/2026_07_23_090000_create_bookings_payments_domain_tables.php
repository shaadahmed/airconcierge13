<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2.3 Bookings & Payments domain tables (adapted from migrations_fresh).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platforms')) {
            Schema::create('platforms', function (Blueprint $table) {
                $table->increments('id');
                $table->string('platform_name', 255)->nullable();
                $table->boolean('deleted')->nullable()->default(false);
            });
        }

        if (! Schema::hasTable('guests')) {
            Schema::create('guests', function (Blueprint $table) {
                $table->increments('id');
                $table->string('guest_name', 255)->nullable();
                $table->string('first_name', 255)->nullable();
                $table->string('last_name', 255)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('city', 255)->nullable();
                $table->string('state', 255)->nullable();
                $table->string('country', 255)->nullable();
                $table->boolean('blacklisted')->nullable()->default(false);
                $table->text('notes')->nullable();
                $table->boolean('deleted')->nullable()->default(false);
            });
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('region_id')->nullable()->index();
                $table->unsignedInteger('subregion_id')->nullable()->index();
                $table->unsignedInteger('property_id')->nullable()->index();
                $table->string('booking_code', 255)->nullable()->index();
                $table->string('month', 2)->nullable();
                $table->string('year', 5)->nullable();
                $table->integer('no_of_nights')->nullable();
                $table->date('booking_date')->nullable();
                $table->date('reservation_start_date')->nullable()->index();
                $table->date('reservation_end_date')->nullable();
                $table->string('checkin_time', 255)->nullable();
                $table->string('checkout_time', 255)->nullable();
                $table->integer('no_of_guests')->nullable();
                $table->unsignedInteger('platform_id')->nullable()->index();
                $table->integer('hostaway_reservation_id')->nullable()->index();
                $table->decimal('accomodations', 11, 2)->nullable()->default(0);
                $table->decimal('cleaning_fee', 11, 2)->nullable()->default(0);
                $table->decimal('tot_charged_to_guest', 11, 2)->nullable()->default(0);
                $table->decimal('total_guest_paid', 11, 2)->nullable()->default(0);
                $table->decimal('management_fee', 11, 2)->nullable()->default(0);
                $table->decimal('owner_payout_amount_from_airconcierge', 11, 2)->nullable()->default(0);
                $table->boolean('cancelled_booking')->nullable()->default(false);
                $table->text('owner_notes')->nullable();
                $table->text('booking_notes')->nullable();
                $table->string('airbnb_reservation_code', 255)->nullable();
                $table->boolean('deleted')->nullable()->default(false);
                $table->boolean('is_payment_clear')->default(true);
                $table->timestamp('dateadded')->nullable();
            });
        }

        if (! Schema::hasTable('guests_bookings')) {
            Schema::create('guests_bookings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('guest_id')->nullable()->index();
                $table->unsignedInteger('booking_id')->nullable()->index();
            });
        }

        if (! Schema::hasTable('booking_alerts')) {
            Schema::create('booking_alerts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('booking_id')->nullable()->index();
                $table->string('alert_type', 100)->nullable();
                $table->text('message')->nullable();
                $table->boolean('resolved')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payment_types')) {
            Schema::create('payment_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->boolean('deleted')->nullable()->default(false);
            });
        }

        if (! Schema::hasTable('incoming_payment_types')) {
            Schema::create('incoming_payment_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->boolean('deleted')->nullable()->default(false);
            });
        }

        if (! Schema::hasTable('booking_payments')) {
            Schema::create('booking_payments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('booking_id')->nullable()->index();
                $table->unsignedInteger('payment_type_id')->nullable();
                $table->decimal('amount', 11, 2)->nullable()->default(0);
                $table->date('payment_date')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('deleted')->nullable()->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_incoming_payments')) {
            Schema::create('booking_incoming_payments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('booking_id')->nullable()->index();
                $table->unsignedInteger('incoming_payment_type_id')->nullable();
                $table->decimal('amount', 11, 2)->nullable()->default(0);
                $table->date('payment_date')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('deleted')->nullable()->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payment_receipts')) {
            Schema::create('payment_receipts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('booking_payment_id')->nullable()->index();
                $table->string('receipt_path', 500)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('property_payment_types')) {
            Schema::create('property_payment_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->boolean('deleted')->nullable()->default(false);
            });
        }

        if (! Schema::hasTable('property_payments')) {
            Schema::create('property_payments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('property_id')->nullable()->index();
                $table->unsignedInteger('property_payment_type_id')->nullable();
                $table->decimal('amount', 11, 2)->nullable()->default(0);
                $table->date('payment_date')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('deleted')->nullable()->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('property_payment_type_fees')) {
            Schema::create('property_payment_type_fees', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('property_payment_type_id')->nullable()->index();
                $table->decimal('fee_amount', 11, 2)->nullable()->default(0);
                $table->string('fee_label', 255)->nullable();
            });
        }

        if (! Schema::hasTable('owners_emails_notifications_log')) {
            Schema::create('owners_emails_notifications_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('booking_id')->nullable()->index();
                $table->unsignedInteger('owner_id')->nullable()->index();
                $table->string('email_type', 100)->nullable();
                $table->string('status', 50)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('owner_blocks')) {
            Schema::create('owner_blocks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('property_id')->nullable()->index();
                $table->unsignedInteger('owner_id')->nullable()->index();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('deleted')->nullable()->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_blocks');
        Schema::dropIfExists('owners_emails_notifications_log');
        Schema::dropIfExists('property_payment_type_fees');
        Schema::dropIfExists('property_payments');
        Schema::dropIfExists('property_payment_types');
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('booking_incoming_payments');
        Schema::dropIfExists('booking_payments');
        Schema::dropIfExists('incoming_payment_types');
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('booking_alerts');
        Schema::dropIfExists('guests_bookings');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('platforms');
    }
};
