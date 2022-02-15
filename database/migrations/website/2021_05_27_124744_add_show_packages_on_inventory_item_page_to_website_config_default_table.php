<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddShowPackagesOnInventoryItemPageToWebsiteConfigDefaultTable extends Migration
{
    private const FILTERS_SHOW_PACKAGES_OPTION = [
        'key' => 'inventory/show_packages_on_inventory_item_page',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Show Packages On The Inventory Item Page',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"1":"On","0":"Off"}',
        'default_label' => 'Off',
        'default_value' => null,
        'sort_order' => 1130,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::FILTERS_SHOW_PACKAGES_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::FILTERS_SHOW_PACKAGES_OPTION['key'])->delete();
    }
}
