<?php

use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddUseProximityDistanceSelectorToBishsWebsiteConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        WebsiteConfig::setKeyValueByDealerId(
            9638,
            'website/use_proximity_distance_selector',
            1
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        WebsiteConfig::setKeyValueByDealerId(
            9638,
            'website/use_proximity_distance_selector',
            0
        );
    }
}
