<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User\DealerLocationService;

use App\Models\User\DealerLocation;
use App\Services\User\DealerLocationService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @covers \App\Services\User\DealerLocationService::moveAndDelete
 * @group DealerLocations
 */
class MoveAndDeleteTest extends TestCase
{
    use WithFaker;

    /**
     * Test that SUT will thrown a not found exception
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has been thrown instead of the desired exception
     */
    public function testWillThrowAModelNotFoundException(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have well known dealer location id
        $locationId = $this->faker->numberBetween(1, 100000);

        // And I know suddenly some exception could be thrown
        $exception = new ModelNotFoundException(sprintf('No query results for model [%s]', DealerLocation::class));

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::get" method is called once with known parameters
        $dependencies->locationRepo->shouldReceive('get')
            ->with(['dealer_location_id' => $locationId])
            ->once()
            ->andThrow($exception);

        // Then I expect to see a specific exception being thrown
        $this->expectException(ModelNotFoundException::class);

        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($exception->getMessage());

        // And I expect that "LoggerServiceInterface::error" method is called once
        $dependencies->loggerService->shouldReceive('error')->once();

        // And I expect that "DealerLocationRepositoryInterface::rollbackTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('rollbackTransaction')->once();

        // When I call the "DealerLocationService::moveAndDelete" method with a known parameter
        $service->moveAndDelete($locationId);
    }

    /**
     * Test that SUT will not move any related record, because it has not any related record, but it will delete one
     * dealer location
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillNotMoveAnyRelatedRecordButWillDelete(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have well known dealer location with certain id
        $locationId = $this->faker->numberBetween(1, 100000);
        $location = self::getEloquentMock(DealerLocation::class);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once with known parameters
        $dependencies->locationRepo->shouldReceive('get')
            ->with(['dealer_location_id' => $locationId])
            ->once()
            ->andReturn($location);

        // And I expect that "DealerLocation::hasRelatedRecords" method is called once returning false
        $location->shouldReceive('hasRelatedRecords')
            ->once()
            ->andReturnFalse();

        // And I expect that "DealerLocationRepositoryInterface::delete" method is called once with known parameters
        // returning one, the number of dealer locations delete
        $dependencies->locationRepo->shouldReceive('delete')
            ->with(['dealer_location_id' => $locationId])
            ->once()
            ->andReturn(1);

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::moveAndDelete" method with a known parameter
        $result = $service->moveAndDelete($locationId);

        // Then I expect that "DealerLocationService::moveAndDelete" returns a known value
        self::assertTrue($result);
    }

    /**
     * Test that SUT will move all related records and it will delete one dealer location
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillMoveAllRelatedRecordAndWillDelete(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have well known dealer location with certain id
        $locationId = $this->faker->numberBetween(1, 100000);
        $location = self::getEloquentMock(DealerLocation::class);

        // And I have target dealer location id
        $targetLocationId = $this->faker->numberBetween(100001, 200000);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once with known parameters
        $dependencies->locationRepo->shouldReceive('get')
            ->with(['dealer_location_id' => $locationId])
            ->once()
            ->andReturn($location);

        // And I expect that "DealerLocation::hasRelatedRecords" method is called once returning true
        $location->shouldReceive('hasRelatedRecords')
            ->once()
            ->andReturnTrue();

        // And I expect that "DealerLocationService::moveRelatedRecords" method is called once with a known parameter
        $service->shouldReceive('moveRelatedRecords')
            ->with($location, $targetLocationId)
            ->once();

        // And I expect that "DealerLocationRepositoryInterface::delete" method is called once with known parameters
        // returning one, the number of dealer locations delete
        $dependencies->locationRepo->shouldReceive('delete')
            ->with(['dealer_location_id' => $locationId])
            ->once()
            ->andReturn(1);

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::moveAndDelete" method with known parameters
        $result = $service->moveAndDelete($locationId, $targetLocationId);

        // Then I expect that "DealerLocationService::moveAndDelete" returns a known value
        self::assertTrue($result);
    }
}
