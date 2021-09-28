<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompletedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_completed_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_email');
            $table->integer('total_amount');
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('event_id');
            $table->string('object_id');
            $table->string('stripe_customer')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ecommerce_completed_orders');
    }
}
