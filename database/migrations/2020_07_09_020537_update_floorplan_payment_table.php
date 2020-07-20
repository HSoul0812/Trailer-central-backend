<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFloorplanPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * All the floorplan payments should be associated to one of Bank type of accounts.
         * So that it can be synced with QBO as a creditCardPayment
         */
        Schema::table('inventory_floor_plan_payment', function (Blueprint $table) {
            $table->integer('account_id');
            $table->integer('qb_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_floor_plan_payment', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->dropColumn('qb_id');
        });
    }
}
