<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Exceptions\Tests\MissingTestDealerLocationIdException;
use App\Exceptions\Tests\MissingTestWebsiteIdException;
use Mockery;
use ReflectionException;
use ReflectionProperty;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MockPrivateMembers;

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
        $locationIds = self::getTestDealerLocationIds();
        return reset($locationIds);
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
        $mock->shouldReceive('fromFloat')->passthru();

        return $mock;
    }

    /**
     * @param Model $model
     * @param string $methodName
     * @param Model|null $relation
     * @return void
     */
    protected function initHasOneRelation(Model $model, string $methodName, ?Model $relation)
    {
        $hasOne = Mockery::mock(HasOne::class);

        $model->shouldReceive('setRelation')->passthru();
        $model->shouldReceive($methodName)->andReturn($hasOne);

        $hasOne->shouldReceive('getResults')->andReturn($relation);
    }

    /**
     * @param Model $model
     * @param string $methodName
     * @param Model|null $relation
     * @return void
     */
    protected function initBelongsToRelation(Model $model, string $methodName, ?Model $relation)
    {
        $belongsTo = Mockery::mock(BelongsTo::class);

        $model->shouldReceive('setRelation')->passthru();
        $model->shouldReceive($methodName)->andReturn($belongsTo);

        $belongsTo->shouldReceive('getResults')->andReturn($relation);
    }

    /**
     * @param string $property
     * @param string $class
     * @return void
     */
    protected function instanceMock(string $property, string $class)
    {
        $this->{$property} = Mockery::mock($class);
        $this->app->instance($class, $this->{$property});
    }

    /**
     * @return CallbackInterface
     */
    public static function getCallback(): CallbackInterface
    {
        return new class() implements CallbackInterface {
            /**
             * @var bool
             */
            private $isCalled = false;

            /**
             * @return \Closure
             */
            public function getClosure(): \Closure
            {
                return function ()  {
                    $this->isCalled = true;
                };
            }

            /**
             * @return bool
             */
            public function isCalled(): bool
            {
                return $this->isCalled;
            }
        };
    }

    /**
     * @param CallbackInterface $callback
     * @param string $message
     */
    public static function assertCalled(CallbackInterface $callback, string $message = ''): void
    {
        if (empty($message)) {
            $message = 'Failed asserting that not called is called';
        }

        self::assertTrue($callback->isCalled(), $message);
    }

    /**
     * @param CallbackInterface $callback
     * @param string $message
     */
    public static function assertNotCalled(CallbackInterface $callback, string $message = ''): void
    {
        if (empty($message)) {
            $message = 'Failed asserting that called is not called.';
        }

        self::assertFalse($callback->isCalled(), $message);
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     * @return void
     * @throws \ReflectionException
     */
    public function setToPrivateProperty($object, $property, $value)
    {
        $reflector = new ReflectionProperty(get_class($object), $property);
        $reflector->setAccessible(true);
        $reflector->setValue($object, $value);
    }

    /**
     * @param $object
     * @param $property
     * @return mixed
     * @throws ReflectionException
     */
    public function getFromPrivateProperty($object, $property)
    {
        $reflector = new ReflectionProperty(get_class($object), $property);
        $reflector->setAccessible(true);

        return $reflector->getValue($object);
    }
}
