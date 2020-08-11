<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function accessToken()
    {
        return env('TESTS_DEFAULT_ACCESS_TOKEN', '123');
    }


    // Get Test Dealer ID
    public static function getTestDealerId() {
        // Get Test Dealer ID
        $dealerId = env('TEST_DEALER_ID');
        if(empty($dealerId)) {
            throw new MissingTestDealerIdException;
        }
        return $dealerId;
    }

    // Get Test Dealer Location ID's
    public static function getTestDealerLocationIds() {
        // Get Location
        $locationId = env('TEST_LOCATION_ID');
        if(empty($dealerId)) {
            throw new MissingTestDealerLocationIdException;
        }
        return explode(",", $locationId);
    }

    // Get Random Test Dealer Location
    public static function getTestDealerLocationRandom() {
        // Get Random Location
        $locationIds = self::getTestDealerLocationIds();
        $locationKey = array_rand($locationIds);
        return $locationIds[$locationKey];
    }

    // Get Test Website ID's
    public static function getTestWebsiteIds() {
        // Get Website
        $websiteId = env('TEST_WEBSITE_ID');
        if(empty($dealerId)) {
            throw new MissingTestWebsiteIdException;
        }
        return explode(",", $websiteId);
    }

    // Get Random Test Website
    public static function getTestWebsiteRandom() {
        // Get Random Website
        $websiteIds = self::getTestWebsiteIds();
        $websiteKey = array_rand($websiteIds);
        return $websiteIds[$websiteKey];
    }
}