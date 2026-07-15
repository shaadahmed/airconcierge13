<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `bookings` from live DB (schema only).
 */
class CreateBookingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('bookings')) {
            return;
        }

        Schema::create('bookings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('region_id')->nullable();
            $table->integer('subregion_id')->nullable();
            $table->integer('property_id')->nullable();
            $table->string('booking_code', 255)->nullable();
            $table->string('month', 2)->nullable();
            $table->string('year', 5)->nullable();
            $table->integer('no_of_nights')->nullable();
            $table->date('booking_date')->nullable();
            $table->date('reservation_start_date')->nullable();
            $table->date('reservation_end_date')->nullable();
            $table->string('checkin_time', 255)->nullable();
            $table->string('checkout_time', 255)->nullable();
            $table->integer('no_of_guests')->nullable();
            $table->integer('platform_id')->nullable();
            $table->decimal('accomodations', 11, 2)->nullable()->default(0.00);
            $table->decimal('cleaning_fee', 11, 2)->nullable()->default(0.00);
            $table->decimal('tot_charged_to_guest', 11, 2)->nullable()->default(0.00);
            $table->decimal('safely_insurance_fee', 11, 2)->nullable()->default(0.00);
            $table->float('ac_insurance_revenue', 11, 2)->nullable()->default(0.00);
            $table->decimal('damage_protection_fee', 11, 2)->nullable()->default(0.00);
            $table->decimal('daily_utility_fee', 11, 2)->nullable()->default(0.00);
            $table->decimal('pet_fee', 11, 2)->nullable()->default(0.00);
            $table->decimal('pet_deposit', 11, 2)->nullable()->default(0.00);
            $table->decimal('security_deposit', 11, 2)->nullable()->default(0.00);
            $table->decimal('security_deposit_deductions', 11, 2)->nullable()->default(0.00);
            $table->decimal('total_guest_paid', 11, 2)->nullable()->default(0.00);
            $table->decimal('site_listing_fee', 11, 2)->nullable()->default(0.00);
            $table->decimal('credit_card_transaction_fee', 11, 2)->nullable()->default(0.00);
            $table->decimal('payment_of_cleaners', 11, 2)->nullable()->default(0.00);
            $table->decimal('concierge_restocking', 11, 2)->nullable()->default(0.00);
            $table->decimal('maintance', 11, 2)->nullable()->default(0.00);
            $table->decimal('accounting_adjustment', 11, 2)->nullable()->default(0.00);
            $table->decimal('damage_protection', 11, 2)->nullable()->default(0.00);
            $table->decimal('refund_pet_deposit', 11, 2)->nullable()->default(0.00);
            $table->decimal('refund_security_deposit', 11, 2)->nullable()->default(0.00);
            $table->decimal('total_paid_by_airconcierge_to_city', 11, 2)->nullable()->default(0.00);
            $table->decimal('management_fee', 11, 2)->nullable()->default(0.00);
            $table->decimal('total_airconcierge_paid_out', 11, 2)->nullable()->default(0.00);
            $table->decimal('total_elect_to_remit', 11, 2)->nullable()->default(0.00);
            $table->decimal('owner_payout_amount_from_airconcierge', 11, 2)->nullable()->default(0.00);
            $table->decimal('owner_payout_amount_from_airbnb', 11, 2)->nullable()->default(0.00);
            $table->boolean('cancelled_booking')->nullable()->default(0);
            $table->text('owner_notes')->nullable();
            $table->text('booking_notes')->nullable();
            $table->string('air_concierge_staff', 255)->nullable();
            $table->decimal('staff_commession', 11, 2)->nullable()->default(0.00);
            $table->decimal('commession_offset', 11, 2)->nullable()->default(0.00);
            $table->text('commession_offset_notes')->nullable();
            $table->decimal('staff_commession_adjusted', 11, 2)->nullable()->default(0.00);
            $table->decimal('convenience_fee', 11, 2)->nullable()->default(0.00);
            $table->string('homes_per_region', 255)->nullable();
            $table->string('homes_serviced', 255)->nullable();
            $table->decimal('average_nightly_rate', 11, 2)->nullable()->default(0.00);
            $table->decimal('virbo_service_fee_in', 11, 2)->nullable()->default(0.00);
            $table->decimal('birbo_service_fee_out', 11, 2)->nullable()->default(0.00);
            $table->decimal('ac_management_fee', 11, 2)->nullable();
            $table->decimal('offset_owner_amount_cohost_res', 11, 2)->nullable()->default(0.00);
            $table->decimal('airbnb_cohost_payout', 11, 2)->nullable()->default(0.00);
            $table->decimal('amount_paid_by_owner', 11, 2)->nullable();
            $table->decimal('accounting_adjustment_income', 11, 2)->nullable()->default(0.00);
            $table->date('date_of_payment_by_owner')->nullable();
            $table->enum('method_of_payment_by_owner', ['Credit Card', 'Direct Deposit', 'Paypal'])->nullable();
            $table->string('airbnb_reservation_code', 255)->nullable();
            $table->decimal('special_offer_amount', 10, 0)->nullable();
            $table->boolean('is_cohost_booking')->nullable()->default(0);
            $table->enum('tot_mode', ['paid_by_airconcierge_to_city', 'paid_at_owners_election', 'charged_to_guest_paid_to_city_by_airbnb', 'none'])->nullable();
            $table->integer('airbnbemail')->nullable()->default(0);
            $table->integer('parent_id')->nullable();
            $table->dateTime('dateadded')->nullable();
            $table->tinyInteger('cleaner_invoiced')->default(0);
            $table->boolean('deleted')->nullable()->default(0);
            $table->boolean('is_payment_clear')->default(1);
            $table->tinyInteger('is_payment_disbursed')->nullable()->default(0);
            $table->string('disbursement_month', 2)->nullable();
            $table->string('disbursement_year', 5)->nullable();
            $table->longText('original_financial_values')->nullable();
            $table->index('property_id', 'bookings_property_id');
            $table->index('platform_id', 'bookings_platform_id');
            $table->index('subregion_id', 'subregion_id');
            $table->index('parent_id', 'parent_id');
            $table->index('region_id', 'region_id');
            $table->index('month', 'month');
            $table->index('year', 'year');
            $table->index('cancelled_booking', 'cancelled_booking');
            $table->index('deleted', 'deleted');
            $table->index(['reservation_start_date', 'reservation_end_date'], 'reservation_start_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
