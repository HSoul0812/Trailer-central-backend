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

    private $websiteConfig;

    public function __construct()
    {
        $this->websiteConfig = DB::table('website')
            ->select('type_config')
            ->where('id', self::GOLF_CART_FOR_SALE_WEBSITE_ID)
            ->first();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = unserialize($this->websiteConfig->type_config);

        $config['filters'] = self::GOLF_CART_FOR_SALE_CUSTOM_CONFIG;

        $this->websiteConfig->update(['type_config' => serialize($config)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $config = unserialize($this->websiteConfig->type_config);

        unset($config['filters']['category']);
        unset($config['filters']['typeLabel']);

        $this->websiteConfig->update(['type_config' => serialize($config)]);
    }
}
