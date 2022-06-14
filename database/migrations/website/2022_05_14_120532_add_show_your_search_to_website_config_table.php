<?php

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddShowYourSearchToWebsiteConfigTable extends Migration
{
    private const BISHS_DOMAIN = 'bishs.com';

    private const INVENTORY_COUNT_CONFIG = [
        'key' => 'website/show_your_search',
        'value' => 1
    ];

    private $dealerId;

    public function __construct()
    {
        if ($website = Website::where('domain', self::BISHS_DOMAIN)->first()) {
            $this->dealerId = $website->dealer_id;
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->dealerId) {
            WebsiteConfig::setKeyValueByDealerId(
                $this->dealerId,
                self::INVENTORY_COUNT_CONFIG['key'],
                self::INVENTORY_COUNT_CONFIG['value']
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
        if ($this->dealerId) {
            WebsiteConfig::setKeyValueByDealerId(
                $this->dealerId,
                self::INVENTORY_COUNT_CONFIG['key'],
                0
            );
        }
    }
}
