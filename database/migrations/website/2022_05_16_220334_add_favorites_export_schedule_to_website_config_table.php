<?php

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddFavoritesExportScheduleToWebsiteConfigTable extends Migration
{
    private const BISHS_DOMAIN = 'bishs.com';

    private const FAVORITES_EXPORT_SCHEDULE = [
        'key' => 'general/favorites_export_schedule',
        'value' => '0'
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
                self::FAVORITES_EXPORT_SCHEDULE['key'],
                self::FAVORITES_EXPORT_SCHEDULE['value']
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
                self::FAVORITES_EXPORT_SCHEDULE['key'],
                null
            );
        }
    }
}
