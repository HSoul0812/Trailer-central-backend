<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User\DealerLocationService;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\User\DealerLocation;
use App\Services\User\DealerLocationService;
use DomainException;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @covers \App\Services\User\DealerLocationService::moveRelatedRecords
 * @group DealerLocations
 */
class MoveRelatedRecordsTest extends TestCase
{
    use WithFaker;

    /**
     * Test that SUT will thrown an exception when there is not a dealer location able to move the related records
     *
     * @dataProvider dealerLocationIdsToMoveProvider
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has been thrown instead of the desired exception
     */
    public function testWillThrowADomainException(?int $locationIdToMove, string $expectedExceptionMessage): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */
        /** @var  MockInterface|LegacyMockInterface|DealerLocation $location */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have well known dealer location
        $location = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        if ($locationIdToMove !== null) {
            // And I expect that "DealerLocationRepositoryInterface::dealerHasLocationWithId" method is called once,
            // with known parameters adn returning false
            $dependencies->locationRepo->shouldReceive('dealerHasLocationWithId')
                ->with($location->dealer_id, $locationIdToMove)
                ->once()
                ->andReturnFalse();
        } else {
            // And I expect that "DealerLocationService::getAnotherAvailableLocationIdToMove" method is called once,
            // with known parameters and returning null
            $service->shouldReceive('getAnotherAvailableLocationIdToMove')
                ->with($location->dealer_location_id, $location->dealer_id)
                ->once()
                ->andReturnNull();
        }

        // Then I expect to see a specific exception being thrown
        $this->expectException(DomainException::class);

        // And I also expect to see an specific exception message
        $expectedExceptionMessage = str_replace(
            [
                '@dealer_id',
                '@dealer_location_id_to_move',
                '@dealer_location_id'
            ],
            [
                $location->dealer_id,
                $locationIdToMove,
                $location->dealer_location_id
            ],
            $expectedExceptionMessage
        );
        $this->expectExceptionMessage($expectedExceptionMessage);

        // And I expect that "LoggerServiceInterface::error" method is called once
        $dependencies->loggerService->shouldReceive('error')->once();

        // And I expect that "DealerLocationRepositoryInterface::rollbackTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('rollbackTransaction')->once();

        // When I call the "DealerLocationService::moveRelatedRecords" method with known parameters
        $service->moveRelatedRecords($location, $locationIdToMove);
    }

    /**
     * Test that SUT will move those related records from a location to another location
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillMoveToAnotherLocation(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */
        /** @var  MockInterface|LegacyMockInterface|DealerLocation $location */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have well known dealer location
        $location = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // And I know I will receive a well known dealer location as a target
        $targetLocation = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationService::getAnotherAvailableLocationIdToMove" method is called once,
        // with known parameters and returning a known location
        $service->shouldReceive('getAnotherAvailableLocationIdToMove')
            ->with($location->dealer_location_id, $location->dealer_id)
            ->once()
            ->andReturn($targetLocation->dealer_location_id);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once with known parameters
        $dependencies->inventoryRepo->shouldReceive('moveLocationId')
            ->with($location->dealer_location_id, $targetLocation->dealer_location_id)
            ->once();

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once with known parameters
        $dependencies->apiEntityReferenceRepo->shouldReceive('updateMultiples')
            ->with(
                [
                    'entity_id' => $location->dealer_location_id,
                    'entity_type' => ApiEntityReference::TYPE_LOCATION
                ],
                [
                    'entity_id' => $targetLocation->dealer_location_id
                ])
            ->once();

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::moveRelatedRecords" method with known parameters
        $service->moveRelatedRecords($location);
    }

    /**
     * @return array<string, array>
     */
    public function dealerLocationIdsToMoveProvider(): array
    {
        return [                            // int $dealerLocationIdToMove, string $expectedExceptionMessage
            'non existent location'          => [88888, "The provided target DealerLocation{dealer_location_id=@dealer_location_id_to_move} doesn't belong the Dealer{dealer_id=@dealer_id}"],
            'dealer with not more locations' => [null, "There isn't a possible location to move those related records of DealerLocation{dealer_location_id=@dealer_location_id}"],
        ];
    }
}
