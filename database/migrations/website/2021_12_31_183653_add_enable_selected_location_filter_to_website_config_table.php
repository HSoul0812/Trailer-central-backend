<?php

use Database\helpers\website\WebsiteConfig;
use Illuminate\Database\Migrations\Migration;

class AddEnableSelectedLocationFilterToWebsiteConfigTable extends Migration
{
    private const FILTER_BY_SELECTED_CONFIG = [
        'key' => 'inventory/enable_selected_location_filter',
        'dealer_names' => ['Happy Trailer Sales','Bish\'s Meridian'],
        'value' => 1
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach (self::FILTER_BY_SELECTED_CONFIG['dealer_names'] as $dealerName) {
            WebsiteConfig::setKeyValueByDealerName(
                $dealerName,
                self::FILTER_BY_SELECTED_CONFIG['key'],
                self::FILTER_BY_SELECTED_CONFIG['value']
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (self::FILTER_BY_SELECTED_CONFIG['dealer_names'] as $dealerName) {
            WebsiteConfig::setKeyValueByDealerName(
                $dealerName,
                self::FILTER_BY_SELECTED_CONFIG['key'],
               0
            );
        }
    }
}
