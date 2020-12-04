<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Integration\Facebook\Page;
use App\Models\Integration\Facebook\Catalog;
use Faker\Generator as Faker;

/**
 * Define Catalog Factory
 */
$factory->define(Catalog::class, function (Faker $faker) {
    // Return Overrides
    $time = time();
    return [
        'id' => $_ENV['TEST_FB_RELATION_ID'],
        'dealer_id' => $_ENV['TEST_DEALER_ID'],
        'dealer_location_id' => $_ENV['TEST_FB_LOCATION_ID'],
        'fbapp_page_id' => $_ENV['TEST_FB_RELATION_ID'],
        'business_id' => $_ENV['TEST_FB_BUSINESS_ID'],
        'catalog_id' => $_ENV['TEST_FB_CATALOG_ID'],
        'account_id' => $_ENV['TEST_FB_ACCOUNT_ID'],
        'account_name' => $_ENV['TEST_FB_ACCOUNT_NAME'],
        'feed_id' => $_ENV['TEST_FB_FEED_ID']
    ];
});

/**
 * Define Page Factory
 */
$factory->define(Page::class, function (Faker $faker) {
    return [
        'id' => $_ENV['TEST_FB_RELATION_ID'],
        'dealer_id' => $_ENV['TEST_DEALER_ID'],
        'page_id' => $_ENV['TEST_FB_PAGE_ID'],
        'title' => $_ENV['TEST_FB_PAGE_TITLE']
    ];
});