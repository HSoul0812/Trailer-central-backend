<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User\DealerLocationService;

use App\Models\User\DealerLocation;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\User\DealerLocationService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @covers \App\Services\User\DealerLocationService::getAnotherAvailableLocationIdToMove
 * @group DealerLocations
 */
class GetAnotherAvailableLocationIdToMoveTest extends TestCase
{
    use WithFaker;

    /**
     * Test that SUT will returns default location id
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillReturnsDefaultOne(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */
        /** @var  MockInterface|LegacyMockInterface|DealerLocation $defaultLocation */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have a dealer location id
        $locationId = $this->faker->numberBetween(1, 100000);

        // And I have a defined default dealer location with a certain id
        $defaultLocation = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000),
            'is_default' => 1
        ]);

        // Then I expect that "DealerLocationRepositoryInterface::getDefaultByDealerId" method is called once
        // with a known parameter, returning a know location instance
        $dependencies->locationRepo->shouldReceive('getDefaultByDealerId')
            ->with($dealerId)
            ->once()
            ->andReturn($defaultLocation);

        // When I call the "DealerLocationService::getAnotherAvailableLocationIdToMove" method with known parameters
        $locationId = $service->getAnotherAvailableLocationIdToMove($locationId, $dealerId);

        // Then I expect that "DealerLocationService::getAnotherAvailableLocationIdToMove" returns a known dealer location id
        self::assertSame($defaultLocation->dealer_location_id, $locationId);
    }

    /**
     * Test that SUT will returns default location id
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillReturnsFirstOne(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */
        /** @var  MockInterface|LegacyMockInterface|Collection<DealerLocation> $locations */
        /** @var  MockInterface|LegacyMockInterface|DealerLocation $firstLocation */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have a dealer location id
        $locationId = $this->faker->numberBetween(1, 100000);

        // And I have a collection of dealer locations
        $locations = factory(DealerLocation::class, $this->faker->numberBetween(5, 15))->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => function (): int {
                return $this->faker->numberBetween(1, 50000);
            }
        ]);

        // And I know  the first dealer location
        $firstLocation = $locations->first();

        // Then I expect that "DealerLocationRepositoryInterface::getDefaultByDealerId" method is called once
        // with a known parameter, returning none
        $dependencies->locationRepo->shouldReceive('getDefaultByDealerId')
            ->with($dealerId)
            ->once()
            ->andReturn(null);

        // And I expect that "DealerLocationRepositoryInterface::findAll" method is called once
        // with a known parameter, returning a collection of dealer locations
        $dependencies->locationRepo->shouldReceive('findAll')
            ->with([
                'dealer_id' => $dealerId,
                DealerLocationRepositoryInterface::CONDITION_AND_WHERE => [['dealer_location_id', '!=', $locationId]]
            ])
            ->once()
            ->andReturn($locations);

        // When I call the "DealerLocationService::getAnotherAvailableLocationIdToMove" method with known parameters
        $locationId = $service->getAnotherAvailableLocationIdToMove($locationId, $dealerId);

        // Then I expect that "DealerLocationService::getAnotherAvailableLocationIdToMove" returns a known dealer location id
        self::assertSame($firstLocation->dealer_location_id, $locationId);
    }

    /**
     * Test that SUT will returns null
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillReturnsNull(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have a dealer location id
        $locationId = $this->faker->numberBetween(1, 100000);

        // Then I expect that "DealerLocationRepositoryInterface::getDefaultByDealerId" method is called once
        // with a known parameter, returning none
        $dependencies->locationRepo->shouldReceive('getDefaultByDealerId')
            ->with($dealerId)
            ->once()
            ->andReturn(null);

        // And I expect that "DealerLocationRepositoryInterface::findAll" method is called once
        // with a known parameter, returning an empty collection
        $dependencies->locationRepo->shouldReceive('findAll')
            ->with([
                'dealer_id' => $dealerId,
                DealerLocationRepositoryInterface::CONDITION_AND_WHERE => [['dealer_location_id', '!=', $locationId]]
            ])
            ->once()
            ->andReturn(Collection::make());

        // When I call the "DealerLocationService::getAnotherAvailableLocationIdToMove" method with known parameters
        $locationId = $service->getAnotherAvailableLocationIdToMove($locationId, $dealerId);

        // Then I expect that "DealerLocationService::getAnotherAvailableLocationIdToMove" returns null
        self::assertNull($locationId);
    }
}
