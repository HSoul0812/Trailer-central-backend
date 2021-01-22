<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Exceptions\Tests\MissingTestDealerLocationIdException;
use App\Exceptions\Tests\MissingTestWebsiteIdException;
use Mockery;

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
            throw new MissingTestDealerIdException();
        }
        return $dealerId;
    }

    // Get Test Dealer Location ID's
    public static function getTestDealerLocationIds() {
        // Get Locations
        $locationId = env('TEST_LOCATION_ID');
        if(empty($locationId)) {
            throw new MissingTestDealerLocationIdException();
        }
        return explode(",", $locationId);
    }

    // Get Test Dealer Location ID
    public static function getTestDealerLocationId() {
        // Get Location
        return reset(self::getTestDealerLocationIds());
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
        if(empty($websiteId)) {
            throw new MissingTestWebsiteIdException();
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

    // Get SMS Number
    public static function getSMSNumber($type = 'valid') {
        // Get Valid Magic Numbers
        $validTypes = array('unavailable', 'unowned', 'full', 'invalid');
        if(!in_array($type, $validTypes)) {
            $type = 'valid';
        }

        // Return Magic Number
        return env('TEST_SMS_' . strtoupper($type));
    }

    public static function getEloquentMock($class)
    {
        $mock = Mockery::mock($class);

        $mock->shouldReceive('setAttribute')->passthru();
        $mock->shouldReceive('getAttribute')->passthru();
        $mock->shouldReceive('hasSetMutator')->passthru();
        $mock->shouldReceive('hasCast')->passthru();
        $mock->shouldReceive('getCasts')->passthru();
        $mock->shouldReceive('getIncrementing')->passthru();
        $mock->shouldReceive('getKeyName')->passthru();
        $mock->shouldReceive('getKeyType')->passthru();
        $mock->shouldReceive('getDates')->passthru();
        $mock->shouldReceive('getCreatedAtColumn')->passthru();
        $mock->shouldReceive('getUpdatedAtColumn')->passthru();
        $mock->shouldReceive('hasSetMutator')->passthru();
        $mock->shouldReceive('usesTimestamps')->passthru();
        $mock->shouldReceive('getAttributeValue')->passthru();
        $mock->shouldReceive('setAttributeValue')->passthru();
        $mock->shouldReceive('hasGetMutator')->passthru();
        $mock->shouldReceive('offsetExists')->passthru();
        $mock->shouldReceive('offsetGet')->passthru();
        $mock->shouldReceive('fromDateTime')->passthru();
        $mock->shouldReceive('getDateFormat')->passthru();
        $mock->shouldReceive('getRelationValue')->passthru();
        $mock->shouldReceive('relationLoaded')->passthru();

        return $mock;
    }
}
