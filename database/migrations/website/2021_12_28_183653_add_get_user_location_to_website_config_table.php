<?php

use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddGetUserLocationToWebsiteConfigTable extends Migration
{
    private const IP_LOCATION_CONFIG = [
        'key' => 'website/get_user_location',
        'dealer_name' => 'Happy Trailer Sales',
        'value' => 1
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        WebsiteConfig::setKeyValueByDealerName(
            self::IP_LOCATION_CONFIG['dealer_name'],
            self::IP_LOCATION_CONFIG['key'],
            self::IP_LOCATION_CONFIG['value']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        WebsiteConfig::setKeyValueByDealerName(
            self::IP_LOCATION_CONFIG['dealer_name'],
            self::IP_LOCATION_CONFIG['key'],
            0
        );
    }
}
