<?php

use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddCalculatorSettingsToBishsWebsiteConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        WebsiteConfig::setKeyValueByDealerName(
            "Bish's Meridian",
            'payment-calculator/terms-per-category',
            json_encode(['trailer' => 14, 'rv' => 14])
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
            "Bish's Meridian",
            'payment-calculator/terms-per-category',
            '{}'
        );
    }
}
