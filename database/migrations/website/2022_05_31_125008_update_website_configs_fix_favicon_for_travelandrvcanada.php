<?php

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class UpdateWebsiteConfigsFixFaviconForTravelandrvcanada extends Migration
{
    private const WEBSITE_DOMAIN = 'travelandrvcanada.com';

    private const FAVICON_CONFIG = [
        'key' => 'general/favicon',
        'value' => 'https://dealer-cdn.com/media/travelandrvcanada/tlrv-favicon.png',
        'old_value' => 'https://dealer-cdn.com/media/travelandrvcanada/favicon-32x32.png'
    ];

    private $dealerId;

    public function __construct()
    {
        if ($website = Website::where('domain', self::WEBSITE_DOMAIN)->first()) {
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
                self::FAVICON_CONFIG['key'],
                self::FAVICON_CONFIG['value']
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
                self::FAVICON_CONFIG['key'],
                self::FAVICON_CONFIG['old_value']
            );
        }
    }
}
