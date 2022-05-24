<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToDealerLocationSalesTaxItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(<<<SQL
            ALTER TABLE `dealer_location_sales_tax_item` ADD `registration_title`
                text
                NULL
                AFTER `settngs`;
            SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_location_sales_tax_item', function (Blueprint $table) {
            $table->dropColumn('registration_title');
        });
    }
}
