<?php

use Illuminate\Database\Migrations\Migration;

class AddGetUserLocationToWebsiteConfigTable extends Migration
{
    private const IP_LOCATION_CONFIG = [
        'key' => 'website/get_user_location',
        'website_id' => 44,
        'value' => 1
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config')->updateOrInsert(
            [

                'website_id' => self::IP_LOCATION_CONFIG['website_id'],
                'key' => self::IP_LOCATION_CONFIG['key'],
            ],
            self::IP_LOCATION_CONFIG
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config')->updateOrInsert(
            [

                'website_id' => self::IP_LOCATION_CONFIG['website_id'],
                'key' => self::IP_LOCATION_CONFIG['key'],
            ],
            array_merge(self::IP_LOCATION_CONFIG, ['value' => 0])
        );
    }
}
