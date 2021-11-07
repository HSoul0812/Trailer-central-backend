<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDontShowUnitsWithoutImagesToWebsiteConfigDefaultTable extends Migration
{
    private const WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION = [
        'key' => 'inventory/images_configuration',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Select whether to show inventory without images or not',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"do_not_show": "Do Not Show Inventory Without Images", "show":"Show Inventory Without Images"}',
        'default_label' => '',
        'default_value' => 'show',
        'sort_order' => 1320,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION['key'])->delete();
    }
}
