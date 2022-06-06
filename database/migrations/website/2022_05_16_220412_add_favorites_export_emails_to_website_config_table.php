<?php

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddFavoritesExportEmailsToWebsiteConfigTable extends Migration
{
    private const BISHS_DOMAIN = 'bishs.com';

    private const FAVORITES_EXPORT_EMAILS = [
        'key' => 'general/favorites_export_emails',
        'value' => 'mwhite@bishs.com;mwalker@bishs.com;blake@trailercentral.com'
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
                self::FAVORITES_EXPORT_EMAILS['key'],
                self::FAVORITES_EXPORT_EMAILS['value']
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
                self::FAVORITES_EXPORT_EMAILS['key'],
                ''
            );
        }
    }
}
