<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripePlanDataToPaymentLog extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->string('plan_key')->nullable();
            $table->string('plan_name')->nullable();
            $table->integer('plan_duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropColumn('plan_key');
            $table->dropColumn('plan_name');
            $table->dropColumn('plan_duration');
        });
    }
}
