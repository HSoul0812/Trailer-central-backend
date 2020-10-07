<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaymentModifyPaymentIntentToText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_payment', function (Blueprint $table) {
            //
            $table->text('related_payment_intent')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_payment', function (Blueprint $table) {
            //
            $table->string('related_payment_intent')->nullable()->change();
        });
    }
}