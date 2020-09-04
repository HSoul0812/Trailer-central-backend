<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingAddressToCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_customer', function (Blueprint $table) {
            $table->string('city', 255)->nullable()->change();
            $table->string('region', 255)->nullable()->change();
            $table->string('postal_code', 30)->nullable()->change();
            $table->string('country', 255)->nullable()->change();
            // Add new columns for shipping address
            $table->tinyInteger('use_same_address')->default(1);
            $table->string('shipping_address',255)->nullable();
            $table->string('shipping_city',255)->nullable();
            $table->string('shipping_region',255)->nullable();
            $table->string('shipping_postal_code',255)->nullable();
            $table->string('shipping_country',255)->nullable();

            $table->dropColumn(['county', 'bill_to', 'ship_to']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_customer', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->change();
            $table->string('region', 2)->nullable()->change();
            $table->string('postal_code', 10)->nullable()->change();
            $table->string('country', 2)->nullable()->change();
            $table->string('county', 255)->nullable();
            $table->text('bill_to')->nullable();
            $table->text('ship_to')->nullable();

            $table->dropColumn([
                'use_same_address',
                'shipping_address',
                'shipping_city',
                'shipping_region',
                'shipping_postal_code',
                'shipping_country'
            ]);
        });
    }
}
