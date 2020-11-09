<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCustomConfigToWebsiteTable extends Migration
{
    private const TRAILER_TRADER_WEBSITE_ID = 284;

    private const GREAT_WEST_TRAILER_CUSTOM_CONFIG = [
        'filterGroup' => [
            [
                'condition' => 'or',
                'filters' => [
                    'dealer_id' => [
                        'neq' => [
                            1041
                        ]
                    ],
                    'status' => [
                        'neq' => [
                            2
                        ]
                    ]
                ]
            ]
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = DB::table('website')
            ->select('type_config')
            ->where('id', self::TRAILER_TRADER_WEBSITE_ID)
            ->first();

        $config = unserialize($config->type_config);

        $config['filters'] = array_merge_recursive($config['filters'], self::GREAT_WEST_TRAILER_CUSTOM_CONFIG);

        DB::table('website')
            ->where('id', self::TRAILER_TRADER_WEBSITE_ID)
            ->update(['type_config' => serialize($config)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $config = DB::table('website')
            ->select('type_config')
            ->where('id', self::TRAILER_TRADER_WEBSITE_ID)
            ->first();

        $config = unserialize($config->type_config);

        unset($config['dealer_config']);

        DB::table('website')
            ->where('id', self::TRAILER_TRADER_WEBSITE_ID)
            ->update(['type_config' => serialize($config)]);
    }
}
