<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User\DealerLocationService;

use App\Models\User\DealerLocation;
use App\Services\User\DealerLocationService;
use Illuminate\Foundation\Testing\WithFaker;
use InvalidArgumentException;
use Mockery;
use Exception;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @covers \App\Services\User\DealerLocationService::create
 * @group DealerLocations
 */
class CreateTest extends TestCase
{
    use WithFaker;

    /**
     * Test that SUT will thrown an exception when some provided parameter like
     * "sales_tax_items" or "fees" were not an array
     *
     * @dataProvider wrongParamsProvider
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has been thrown instead of the desired exception
     */
    public function testWillThrowAnExceptionBecauseSomeWrongParameter(array $params, string $expectedExceptionMessage): void
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

        // Then I know that I will receive an expected location
        $location = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // And I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::create" method is called once, with known parameters
        // returning a known "DealerLocation" instance
        $dependencies->locationRepo
            ->shouldReceive('create')
            ->with($params + ['sales_tax_item_column_titles' => [], 'dealer_id' => $dealerId])
            ->once()
            ->andReturn($location);

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::create" method is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('create')
            ->with($params + ['dealer_location_id' => $location->dealer_location_id])
            ->once();

        // Then I expect to see a specific exception being thrown
        $this->expectException(InvalidArgumentException::class);

        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        // And I expect that "LoggerServiceInterface::error" method is called once
        $dependencies->loggerService->shouldReceive('error')->once();

        // And I expect that "DealerLocationRepositoryInterface::rollbackTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('rollbackTransaction')->once();

        // When I call the "DealerLocationService::create" method
        $service->create($dealerId, $params);
    }

    /**
     * Test that SUT will try updating to zero the "is_default" and "is_default_for_invoice" column of any dealer location
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillTryTurningOffAnyDefaultLocation(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */
        /** @var  MockInterface|LegacyMockInterface|DealerLocation $expectedLocation */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have the parameter "is_default_for_invoice" and "is_default" checked
        $params = ['is_default_for_invoice' => 1, 'is_default' => 1];

        // Then I know that I will receive an expected location
        $expectedLocation = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // And I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::turnOffDefaultLocationByDealerId" method
        // is called once with a known parameter
        $dependencies->locationRepo->shouldReceive('turnOffDefaultLocationByDealerId')->with($dealerId)->once();

        // And I expect that "DealerLocationRepositoryInterface::turnOffDefaultLocationForInvoicingByDealerId" method
        // is called once with a known parameter
        $dependencies->locationRepo->shouldReceive('turnOffDefaultLocationForInvoicingByDealerId')->with($dealerId)->once();

        // And I expect that "DealerLocationRepositoryInterface::create" method is called once, with known parameters
        // returning a known "DealerLocation" instance
        $dependencies->locationRepo
            ->shouldReceive('create')
            ->with($params + ['sales_tax_item_column_titles' => [], 'dealer_id' => $dealerId])
            ->once()
            ->andReturn($expectedLocation);

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::create" method is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('create')
            ->with($params + ['dealer_location_id' => $expectedLocation->dealer_location_id])
            ->once();

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::create" method with known parameters
        $location = $service->create($dealerId, $params);

        // Then I expect that "DealerLocationService::create" returns a known location instance
        $this->assertSame($expectedLocation, $location);
    }

    /**
     * Test that SUT will create certainly number of tax items
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillCreateTaxItems(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */
        /** @var  MockInterface|LegacyMockInterface|DealerLocation $expectedLocation */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have the parameter "sales_tax_items" having three tax items
        $params = ['sales_tax_items' => [
            ['entity_type_id' => 5, 'item_type' => 'state'],
            ['entity_type_id' => 5, 'item_type' => 'local'],
            ['entity_type_id' => 5, 'item_type' => 'county']
        ]];

        // Then I know that I will receive an expected location
        $expectedLocation = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // And I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::create" method is called once, with known parameters
        // returning a known "DealerLocation" instance
        $dependencies->locationRepo
            ->shouldReceive('create')
            ->with($params + ['sales_tax_item_column_titles' => [], 'dealer_id' => $dealerId])
            ->once()
            ->andReturn($expectedLocation);

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::create" method is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('create')
            ->with($params + ['dealer_location_id' => $expectedLocation->dealer_location_id])
            ->once();

        // And I expect that "DealerLocationSalesTaxItemRepositoryInterface::createV1" method is called three times, with known parameters
        foreach ($params['sales_tax_items'] as $item) {
            $dependencies->salesTaxItemRepo
                ->shouldReceive('create')
                ->with($item + ['dealer_location_id' => $expectedLocation->dealer_location_id])
                ->once();
        }

        $dependencies->salesTaxItemRepo
            ->shouldReceive('createV1')
            ->never();

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::create" method with known parameters
        $location = $service->create($dealerId, $params);

        // Then I expect that "DealerLocationService::create" returns a known location instance
        $this->assertSame($expectedLocation, $location);
    }

    /**
     * Test that SUT will create certainly number of quote fees
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillCreateQuoteFees(): void
    {
        /** @var  MockInterface|LegacyMockInterface|DealerLocationService $service */
        /** @var  MockInterface|LegacyMockInterface|DealerLocation $expectedLocation */

        // Given I have the seven dependencies for "DealerLocationService"
        $dependencies = new DealerLocationServiceDependencies();

        // And I have a well constructed "DealerLocationService"
        $service = Mockery::mock(DealerLocationService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I have a dealer id
        $dealerId = $this->faker->numberBetween(1, 100000);

        // And I have the parameter "fees" having five quote fees
        $params = ['fees' => [
            ['title' => 'Bank fee', 'fee_type' => 'bank_fee'],
            ['title' => 'License fee', 'fee_type' => 'license_fee'],
            ['title' => 'Environmental fee', 'fee_type' => 'environmental_fee'],
            ['title' => 'Docs fee', 'fee_type' => 'docs_fee'],
            ['title' => 'Shop supply fee', 'fee_type' => 'shop_supply_fee']
        ]];

        // Then I know that I will receive an expected location
        $expectedLocation = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // And I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::create" method is called once, with known parameters
        // returning a known "DealerLocation" instance
        $dependencies->locationRepo
            ->shouldReceive('create')
            ->with($params + ['sales_tax_item_column_titles' => [], 'dealer_id' => $dealerId])
            ->once()
            ->andReturn($expectedLocation);

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::create" method is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('create')
            ->with($params + ['dealer_location_id' => $expectedLocation->dealer_location_id])
            ->once();

        // And I expect that "DealerLocationQuoteFeeRepository::create" method is called five times, with known parameters
        foreach ($params['fees'] as $fee) {
            $dependencies->quoteFeeRepo
                ->shouldReceive('create')
                ->with($fee + ['dealer_location_id' => $expectedLocation->dealer_location_id])
                ->once();
        }

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::create" method with known parameters
        $location = $service->create($dealerId, $params);

        // Then I expect that "DealerLocationService::create" returns a known location instance
        $this->assertSame($expectedLocation, $location);
    }

    /**
     * @return array<string, array>
     */
    public function wrongParamsProvider(): array
    {
        return [                                // array $params, string $expectedExceptionMessage
            'wrong "sales_tax_items" parameter' => [['sales_tax_items' => true], '"sales_tax_items" must be an array'],
            'wrong "fees" parameter'            => [['fees' => true], '"fees" must be an array'],
        ];
    }
}
