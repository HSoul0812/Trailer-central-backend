<?php

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddSaveSearchToBishsWebsiteConfigTable extends Migration
{
    private const BISHS_DOMAIN = 'bishs.com';

    private const SHOW_SAVE_SEARCH_CONFIG = [
        'key' => 'website/show_save_search',
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
                self::SHOW_SAVE_SEARCH_CONFIG['key'],
                self::SHOW_SAVE_SEARCH_CONFIG['value']
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
                self::SHOW_SAVE_SEARCH_CONFIG['key'],
                0
            );
        }
    }
}
