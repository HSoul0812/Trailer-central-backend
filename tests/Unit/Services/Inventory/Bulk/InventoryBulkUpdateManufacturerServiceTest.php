<?php

namespace Tests\Unit\Services\Inventory\Bulk;

use Mockery;
use Tests\TestCase;

use Illuminate\Support\Facades\Log;

use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\InventoryBulkUpdateManufacturerService;

/**
 * Test for App\Services\Inventory\InventoryBulkUpdateManufacturerService
 *
 * Class InventoryBulkUpdateManufacturerServiceTest
 * @package Tests\Unit\Services\Inventory\Bulk
 *
 * @coversDefaultClass \App\Services\Inventory\InventoryBulkUpdateManufacturerService
 */
class InventoryBulkUpdateManufacturerServiceTest extends TestCase {

    /**
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepositoryMock;

    /**
     * @var LegacyMockInterface|InventoryRepositoryInterface
     */
    private $inventoryRepositoryMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);

        $this->inventoryRepositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryRepositoryMock);
    }

    /**
     * Update manufacturer from given inventories
     *
     * @covers ::update
     *
     * @dataProvider validDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_INVENTORY_BULK_UPDATE_MANUFACTURER
     */
    public function testBulkUpdateManufacturers($params)
    {
        $this->inventoryRepositoryMock
            ->shouldReceive('bulkUpdate')
            ->with(
                ['manufacturer' => $params['from_manufacturer']],
                ['manufacturer' => $params['to_manufacturer']]
            )
            ->once()
            ->andReturn(true);

        Log::shouldReceive('info')->with('Inventory manufacturers updated successfully', $params);

        /** @var InventoryBulkUpdateManufacturerService $service */
        $inventoryBulkUpdateManufacturerService = app()->make(InventoryBulkUpdateManufacturerService::class);
        $result = $inventoryBulkUpdateManufacturerService->update($params);

        $this->assertTrue($result);
    }

    /**
     * Update manufacturer from given inventories
     *
     * @covers ::update
     *
     * @dataProvider invalidDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_INVENTORY_BULK_UPDATE_MANUFACTURER
     */
    public function testBulkUpdateManufacturersWithoutValues($params)
    {
        Log::shouldReceive('error');
        $this->expectException(\Exception::class);

        if (empty($params['from_manufacturer'])) {
            $this->expectExceptionMessage(
                'Value from_manufacturer is required.'
            );
        }

        if (empty($params['to_manufacturer'])) {
            $this->expectExceptionMessage(
                'Value to_manufacturer is required.'
            );
        }

        /** @var InventoryBulkUpdateManufacturerService $service */
        $inventoryBulkUpdateManufacturerService = app()->make(InventoryBulkUpdateManufacturerService::class);
        $result = $inventoryBulkUpdateManufacturerService->update($params);

        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return array[]
     */
    public function validDataProvider(): array
    {
        return [
            'Update Manufacturer' => [
                'params' => [
                    'from_manufacturer' => 'Testing One',
                    'to_manufacturer'   => 'Testing Two'
                ]
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function invalidDataProvider(): array
    {
        return [
            'Update Manufacturer without from_manufacturer' => [
                'params' => [
                    'to_manufacturer'   => 'Testing Two'
                ]
            ],
            'Update Manufacturer without to_manufacturer' => [
                'params' => [
                    'from_manufacturer' => 'Testing One'
                ]
            ]
        ];
    }
}
