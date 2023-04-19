<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFbappListings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `fbapp_listings` MODIFY COLUMN `year` smallInt(4)");

        DB::statement("UPDATE fbapp_listings
LEFT JOIN inventory ON fbapp_listings.inventory_id = inventory.inventory_id
SET fbapp_listings.make = IFNULL(inventory.manufacturer, 'n/a'),
    fbapp_listings.year = IFNULL(inventory.year, 2000),
    fbapp_listings.model = IFNULL(inventory.model, 'n/a')
WHERE fbapp_listings.id > 0;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `fbapp_listings` MODIFY COLUMN `year` tinyInt");
    }
}
