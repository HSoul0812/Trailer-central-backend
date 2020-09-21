<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShowStockOverlayOnStockPhotosToWebsiteConfigDefaultTable extends Migration
{
    private const SHOW_STOCK_OVERLAY_ON_STOCK_PHOTOS_OPTION = [
        'key' => 'inventory/show_stock_overlay_on_stock_photos',
        'private' => 0,
        'type' => 'checkbox',
        'label' => 'Show a stock overlay on the stock photos from "Incoming Feed"',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => null,
        'default_label' => '',
        'default_value' => null,
        'sort_order' => 1070,
    ];

    private const COLTON_WEBSITE_ID = 1090;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::SHOW_STOCK_OVERLAY_ON_STOCK_PHOTOS_OPTION);

        $websiteConfigParams = [
            'website_id' => self::COLTON_WEBSITE_ID,
            'key' => self::SHOW_STOCK_OVERLAY_ON_STOCK_PHOTOS_OPTION['key'],
            'value' => 1
        ];

        DB::table('website_config')->insert($websiteConfigParams);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::SHOW_STOCK_OVERLAY_ON_STOCK_PHOTOS_OPTION['key'])->delete();
    }
}
