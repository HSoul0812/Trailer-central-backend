<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShowSoldUnitInSearchToWebsiteConfigDefaultTable extends Migration
{
    private const SHOW_SOLD_UNIT_IN_SEARCH_OPTION = [
        'key' => 'inventory/show_sold_unit_in_search',
        'private' => 0,
        'type' => 'checkbox',
        'label' => 'Show Sold Units in Search',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => null,
        'default_label' => '',
        'default_value' => null,
        'sort_order' => 1060,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::SHOW_SOLD_UNIT_IN_SEARCH_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::SHOW_SOLD_UNIT_IN_SEARCH_OPTION['key'])->delete();
    }
}
