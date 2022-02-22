<?php

use Illuminate\Database\Migrations\Migration;
use Database\helpers\website\WebsiteConfig;

class AddSuppressDescriptionFromSearchSortingByRelevanceToGreatamericanrvWebsiteConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        WebsiteConfig::setKeyValueByDealerId(
            9088,
            'inventory/include_description_on_search',
            0
        );

        WebsiteConfig::setKeyValueByDealerId(
            9088,
            'inventory/sort_by_relevance',
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
            9088,
            'inventory/include_description_on_search',
            1
        );

        WebsiteConfig::setKeyValueByDealerId(
            9088,
            'inventory/sort_by_relevance',
            0
        );
    }
}
