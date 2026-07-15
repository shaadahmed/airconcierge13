<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline create for `property_payments` from live DB (schema only).
 */
class CreatePropertyPaymentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_payments')) {
            return;
        }

        Schema::create('property_payments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('property_id')->nullable();
            $table->integer('payment_type_id')->nullable();
            $table->string('payment_type_category', 255)->nullable();
            $table->string('payment_type_subcategory', 255)->nullable();
            $table->integer('parent_id')->nullable();
            $table->decimal('price', 11, 2)->nullable();
            $table->decimal('convenience_fee', 11, 2)->default(0.00);
            $table->decimal('management_markup', 11, 2)->default(0.00);
            $table->date('payment_date')->nullable();
            $table->boolean('payment_status')->default(1);
            $table->string('vendor_name', 50)->nullable();
            $table->integer('vendor_id');
            $table->string('receipt', 255)->nullable();
            $table->text('payment_notes')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('is_chain_unlinked')->default(0);
            $table->integer('property_payment_type')->nullable();
            $table->integer('purchaser_id')->nullable();
            $table->index('property_id', 'property_payments_payment_id');
            $table->index('payment_date', 'payment_date');
            $table->index('parent_id', 'parent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_payments');
    }
}
