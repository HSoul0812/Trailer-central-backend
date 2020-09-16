<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIncomeDownPaymentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined','deposit_down_payment','income_down_payment') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");

        Schema::table('qb_payment', function (Blueprint $table) {
            $table->tinyInteger('income_down_payment')->default(0)->after('related_payment_intent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined','down_payment') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");

        Schema::table('qb_payment', function (Blueprint $table) {
            $table->dropColumn('income_down_payment');
        });
    }
}
