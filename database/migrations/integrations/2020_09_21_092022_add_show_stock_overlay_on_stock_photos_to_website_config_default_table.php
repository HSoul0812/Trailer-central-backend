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

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::SHOW_STOCK_OVERLAY_ON_STOCK_PHOTOS_OPTION);

        $result = DB::select(DB::raw("
            SELECT d.dealer_id, COUNT(i.inventory_id) count_with_3, ii.count_without_3 count_without_3
            FROM inventory i
            JOIN dealer d ON d.dealer_id = i.dealer_id
            JOIN (
                SELECT dealer_id, COUNT(inventory_id) count_without_3
                FROM inventory WHERE entity_type_id != 3
                GROUP BY dealer_id
            ) ii ON ii.dealer_id = d.dealer_id
            WHERE i.entity_type_id = 3
            GROUP BY d.dealer_id
            HAVING count_with_3 > count_without_3
        "));

        $dealerIds = array_column(array_map(function ($item) {
            return (array)$item;
        }, $result), 'dealer_id');

        $websiteIds = DB::table('website')
            ->select('id')
            ->whereIn('dealer_id', $dealerIds)
            ->pluck('id');

        foreach ($websiteIds as $websiteId) {
            $websiteConfigParams = [
                'website_id' => $websiteId,
                'key' => self::SHOW_STOCK_OVERLAY_ON_STOCK_PHOTOS_OPTION['key'],
                'value' => 1
            ];

            DB::table('website_config')->insert($websiteConfigParams);
        }

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
