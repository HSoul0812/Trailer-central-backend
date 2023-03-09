<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User\DealerLocationService;

use App\Models\User\DealerLocation;
use App\Services\User\DealerLocationService;
use Illuminate\Foundation\Testing\WithFaker;
use InvalidArgumentException;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;
use Exception;

/**
 * @covers \App\Services\User\DealerLocationService::update
 * @group DealerLocations
 */
class UpdateTest extends TestCase
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

        // And I have well known dealer location
        $location = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::update" method is called once, with known parameters
        $dependencies->locationRepo
            ->shouldReceive('update')
            ->with($params + ['dealer_location_id' => $location->dealer_location_id, 'sales_tax_item_column_titles' => []])
            ->once();

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::updateOrCreateByDealerLocationId" method
        // is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('updateOrCreateByDealerLocationId')
            ->with($location->dealer_location_id, $params)
            ->once();

        // And I expect to see a specific exception being thrown
        $this->expectException(InvalidArgumentException::class);

        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        // And I expect that "LoggerServiceInterface::error" method is called once
        $dependencies->loggerService->shouldReceive('error')->once();

        // And I expect that "DealerLocationRepositoryInterface::rollbackTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('rollbackTransaction')->once();

        // When I call the "DealerLocationService::update" method with known parameters
        $service->update($location->dealer_location_id, $dealerId, $params);
    }

    /**
     * Test that SUT will create a new dealer location setting it up as default location and turning off others
     * locations belonging to that dealer
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillTryTurningOffAnyDefaultLocation(): void
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

        // And I have the parameter "is_default_for_invoice" and "is_default" checked
        $params = ['is_default_for_invoice' => 1, 'is_default' => 1];

        // And I have well known dealer location
        $location = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::turnOffDefaultLocationByDealerId" method
        // is called once with a known parameter
        $dependencies->locationRepo->shouldReceive('turnOffDefaultLocationByDealerId')->with($dealerId)->once();

        // And I expect that "DealerLocationRepositoryInterface::turnOffDefaultLocationForInvoicingByDealerId" method
        // is called once with a known parameter
        $dependencies->locationRepo->shouldReceive('turnOffDefaultLocationForInvoicingByDealerId')->with($dealerId)->once();

        // And I expect that "DealerLocationRepositoryInterface::update" method is called once, with known parameters
        $dependencies->locationRepo
            ->shouldReceive('update')
            ->with($params + ['dealer_location_id' => $location->dealer_location_id, 'sales_tax_item_column_titles' => []])
            ->once();

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::updateOrCreateByDealerLocationId" method
        // is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('updateOrCreateByDealerLocationId')
            ->with($location->dealer_location_id, $params)
            ->once();

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::update" method with known parameters
        $result = $service->update($location->dealer_location_id, $dealerId, $params);

        // Then I expect that "DealerLocationService::update" returns true
        self::assertInstanceOf(DealerLocation::class, $result);
    }

    /**
     * Test that SUT will update certainly number of tax items
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillUpdateTaxItems(): void
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

        // And I have the parameter "sales_tax_items" having three tax items
        $params = ['sales_tax_items' => [
            ['entity_type_id' => 5, 'item_type' => 'state'],
            ['entity_type_id' => 5, 'item_type' => 'local'],
            ['entity_type_id' => 5, 'item_type' => 'county']
        ]];

        // And I have well known dealer location
        $location = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::update" method is called once, with known parameters
        $dependencies->locationRepo
            ->shouldReceive('update')
            ->with($params + ['dealer_location_id' => $location->dealer_location_id, 'sales_tax_item_column_titles' => []])
            ->once();

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::updateOrCreateByDealerLocationId" method
        // is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('updateOrCreateByDealerLocationId')
            ->with($location->dealer_location_id, $params)
            ->once();

        // And I expect that "DealerLocationSalesTaxItemRepositoryInterface::deleteByDealerLocationId"
        // method is called once, with known parameters
        $dependencies->salesTaxItemRepo
            ->shouldReceive('deleteByDealerLocationId')
            ->with($location->dealer_location_id)
            ->once();
        // And I expect that "DealerLocationSalesTaxItemRepositoryInterface::deleteByDealerLocationIdV1"
        // method is called once, with known parameters
        $dependencies->salesTaxItemRepo
            ->shouldReceive('deleteByDealerLocationIdV1')
            ->with($location->dealer_location_id)
            ->once();

        // And I expect that "DealerLocationSalesTaxItemRepositoryInterface::create" method is called three time, with known parameters
        // And I expect that "DealerLocationSalesTaxItemRepositoryInterface::createV1" method is called three time, with known parameters
        foreach ($params['sales_tax_items'] as $item) {
            $dependencies->salesTaxItemRepo
                ->shouldReceive('create')
                ->with($item + ['dealer_location_id' => $location->dealer_location_id])
                ->once();
        }

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::update" method with known parameters
        $result = $service->update($location->dealer_location_id, $dealerId, $params);

        // Then I expect that "DealerLocationService::update" returns true
        self::assertInstanceOf(DealerLocation::class, $result);
    }

    /**
     * Test that SUT will update certainly number of quote fees
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @throws Exception when an unexpected exception has not been handled
     */
    public function testWillUpdateQuoteFees(): void
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

        // And I have the parameter "fees" having five quote fees
        $params = ['fees' => [
            ['title' => 'Bank fee', 'fee_type' => 'bank_fee'],
            ['title' => 'License fee', 'fee_type' => 'license_fee'],
            ['title' => 'Environmental fee', 'fee_type' => 'environmental_fee'],
            ['title' => 'Docs fee', 'fee_type' => 'docs_fee'],
            ['title' => 'Shop supply fee', 'fee_type' => 'shop_supply_fee']
        ]];

        // And I have well known dealer location
        $location = factory(DealerLocation::class)->make([
            'dealer_id' => $dealerId,
            'dealer_location_id' => $this->faker->numberBetween(1, 50000)
        ]);

        // Then I expect that "DealerLocationRepositoryInterface::beginTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('beginTransaction')->once();

        // And I expect that "DealerLocationRepositoryInterface::update" method is called once, with known parameters
        $dependencies->locationRepo
            ->shouldReceive('update')
            ->with($params + ['dealer_location_id' => $location->dealer_location_id, 'sales_tax_item_column_titles' => []])
            ->once();

        // And I expect that "DealerLocationSalesTaxRepositoryInterface::updateOrCreateByDealerLocationId" method
        // is called once, with known parameters
        $dependencies->salesTaxRepo
            ->shouldReceive('updateOrCreateByDealerLocationId')
            ->with($location->dealer_location_id, $params)
            ->once();

        // And I expect that "DealerLocationQuoteFeeRepository::deleteByDealerLocationId" method is called once, with known parameters
        $dependencies->quoteFeeRepo
            ->shouldReceive('deleteByDealerLocationId')
            ->with($location->dealer_location_id)
            ->once();

        // And I expect that "DealerLocationQuoteFeeRepository::create" method is called five times, with known parameters
        foreach ($params['fees'] as $fee) {
            $dependencies->quoteFeeRepo
                ->shouldReceive('create')
                ->with($fee + ['dealer_location_id' => $location->dealer_location_id])
                ->once();
        }

        // And I expect that "DealerLocationRepositoryInterface::commitTransaction" method is called once
        $dependencies->locationRepo->shouldReceive('commitTransaction')->once();

        // When I call the "DealerLocationService::update" method with known parameters
        $result = $service->update($location->dealer_location_id, $dealerId, $params);

        // Then I expect that "DealerLocationService::update" returns true
        self::assertInstanceOf(DealerLocation::class, $result);
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
