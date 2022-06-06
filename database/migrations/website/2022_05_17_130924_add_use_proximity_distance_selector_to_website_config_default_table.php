<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUseProximityDistanceSelectorToWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_COUNT_FOR_ALL_LOCATIONS = [
        'key' => 'website/show_inventory_count_for_all_locations',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Show Inventory Count For All Locations',
        'note' => null,
        'grouping' => 'Home Page Display',
        'values' => '{"0":"No","1":"Yes"}',
        'default_label' => 'No',
        'default_value' => null,
        'sort_order' => 1322,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::INVENTORY_COUNT_FOR_ALL_LOCATIONS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::INVENTORY_COUNT_FOR_ALL_LOCATIONS['key'])->delete();
    }
}
