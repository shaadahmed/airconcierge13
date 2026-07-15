<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `properties` from live DB (schema only).
 */
class CreatePropertiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('properties')) {
            return;
        }

        Schema::create('properties', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('region_id')->nullable();
            $table->integer('subregion_id')->nullable();
            $table->integer('hostaway_listing_id')->nullable();
            $table->string('property_title', 255)->nullable();
            $table->enum('limit_type', ['NIGHTS', 'RESERVATIONS'])->nullable();
            $table->smallInteger('limit_value')->nullable();
            $table->string('email_title', 255)->nullable();
            $table->string('unit_number', 10)->nullable();
            $table->string('street_address', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->timestamp('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('modified_date')->nullable();
            $table->integer('last_modified_by')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->date('inactive_date')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('contract_end_reason', 255)->nullable();
            $table->integer('management_type_id')->default(1);
            $table->decimal('ac_management_fee', 11, 2)->nullable()->default(0.00);
            $table->integer('bathrooms')->nullable()->default(0);
            $table->integer('bedrooms')->nullable()->default(0);
            $table->decimal('owner_monthly_costs', 11, 2)->nullable();
            $table->boolean('tot_paid_at_owner_election')->nullable()->default(0);
            $table->boolean('tot_charged_to_guest_paid_to_city_by_airbnb')->nullable()->default(0);
            $table->boolean('co_host')->nullable()->default(0);
            $table->enum('tot_mode', ['paid_by_airconcierge_to_city', 'paid_at_owners_election', 'charged_to_guest_paid_to_city_by_airbnb', 'none'])->nullable();
            $table->integer('tot_method')->nullable();
            $table->integer('vrbo_tot_mode')->nullable();
            $table->integer('vrbo_tot_method')->nullable();
            $table->boolean('owners_pays_cleaners')->nullable()->default(0);
            $table->boolean('airconcierge_pays_cleaners')->nullable()->default(0);
            $table->boolean('primary_residence')->nullable()->default(0);
            $table->boolean('secondary_residence')->nullable()->default(0);
            $table->boolean('investment_property_only')->nullable()->default(0);
            $table->decimal('owners_montly_cost', 11, 2)->nullable();
            $table->text('management_notes')->nullable();
            $table->string('property_code', 255)->nullable();
            $table->boolean('property_cohost')->nullable()->default(0);
            $table->enum('additional_fee_type', ['community_fee', 'resort_fee', 'daily_use_fee', 'other'])->nullable()->default('community_fee');
            $table->decimal('additional_fee', 11, 2)->nullable();
            $table->decimal('exit_cleaning_fee', 10, 0)->default(0);
            $table->enum('additional_fee_currency_type', ['doller', 'percentage'])->nullable()->default('percentage');
            $table->enum('additional_fee_application', ['daily', 'per_reservation'])->nullable();
            $table->date('permit_date')->nullable();
            $table->enum('payment_method', ['Direct Deposit', 'Direct Deposit (Co Host)', 'Credit Card (Co Host)', 'Airbnb Co Host', 'PayPal'])->nullable();
            $table->boolean('apply_resort_fee')->nullable()->default(0);
            $table->string('supportemail', 256)->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('property_image_url', 255)->nullable();
            $table->boolean('deleted')->nullable()->default(0);
            $table->index('region_id', 'properties_region_id');
            $table->index('created_by', 'properties_created_by');
            $table->index('last_modified_by', 'properties_last_modified_by');
            $table->index('subregion_id', 'subregion_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('properties');
    }
}
