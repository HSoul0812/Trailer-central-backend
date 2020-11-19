<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateFeeAccountingClass extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `dealer_location_quote_fee` CHANGE `accounting_class` `accounting_class` ENUM('Adt Default Fees','Taxes & Fees Group 1','Taxes & Fees Group 2','Taxes & Fees Group 3') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Adt Default Fees';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `dealer_location_quote_fee` CHANGE `accounting_class` `accounting_class` ENUM('Adt Default Fees') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Adt Default Fees';");
    }
}
