<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateWebsitesTypeConfigForGolfcartForsale extends Migration
{
    private const GOLF_CART_FOR_SALE_WEBSITE_ID = 493;

    private const GOLF_CART_FOR_SALE_CUSTOM_CONFIG = [
        "category" => [
            "eq" => [
                [
                    "golf_cart",
                ],
            ],
        ],
        "typeLabel" => [
            "eq" => [
                [
                    "Sports Vehicle"
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
        $websiteConfig = DB::table('website')
            ->select('type_config')
            ->where('id', self::GOLF_CART_FOR_SALE_WEBSITE_ID)
            ->first();

        $config = unserialize($websiteConfig->type_config);

        $config['filters'] = self::GOLF_CART_FOR_SALE_CUSTOM_CONFIG;

        DB::table('website')
            ->where('id', self::GOLF_CART_FOR_SALE_WEBSITE_ID)
            ->update(['type_config' => serialize($config)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $websiteConfig = DB::table('website')
            ->select('type_config')
            ->where('id', self::GOLF_CART_FOR_SALE_WEBSITE_ID)
            ->first();

        $config = unserialize($websiteConfig->type_config);

        unset($config['filters']['category']);
        unset($config['filters']['typeLabel']);

        DB::table('website')
            ->where('id', self::GOLF_CART_FOR_SALE_WEBSITE_ID)
            ->update(['type_config' => serialize($config)]);
    }
}
