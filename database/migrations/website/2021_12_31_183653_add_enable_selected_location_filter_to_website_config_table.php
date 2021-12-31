<?php

use Illuminate\Database\Migrations\Migration;

class AddEnableSelectedLocationFilterToWebsiteConfigTable extends Migration
{
    private const FILTER_BY_SELECTED_CONFIG = [
        'key' => 'inventory/enable_selected_location_filter',
        'website_id' => [44, 1322], // Happy and Bish websites
        'value' => 1
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::FILTER_BY_SELECTED_CONFIG['website_id'] as $websiteId) {
            $composeKey = [
                'website_id' => $websiteId,
                'key' => self::FILTER_BY_SELECTED_CONFIG['key'],
            ];

            DB::table('website_config')->updateOrInsert(
                $composeKey,
                $composeKey + ['value' => self::FILTER_BY_SELECTED_CONFIG['value']]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::FILTER_BY_SELECTED_CONFIG['website_id'] as $websiteId) {
            $composeKey = [

                'website_id' => $websiteId,
                'key' => self::FILTER_BY_SELECTED_CONFIG['key'],
            ];

            DB::table('website_config')->updateOrInsert($composeKey, $composeKey + ['value' => 0]);
        }
    }
}
