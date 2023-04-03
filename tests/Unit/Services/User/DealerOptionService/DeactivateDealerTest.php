<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\DealerOptionsService;
use Mockery;
use Tests\TestCase;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use Illuminate\Support\Collection;

/**
 * Test for App\Services\User\DealerOptionsService
 *
 * class DeactivateDealerTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class DeactivateDealerTest extends TestCase
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);

        $this->inventoryRepository = Mockery::mock(InventoryRepositoryInterface::class);
        $this->app->instance(InventoryRepositoryInterface::class, $this->inventoryRepository);
    }

    /**
     * @covers ::deactivateDealer
     *
     * @dataProvider validDataProviderForDeactivateDealer
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testDeactivateDealer($dealerId)
    {
        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $inventoryParams = [
            'active' => $service::INACTIVE,
            'is_archived' => $service::ARCHIVED_ON,
            'archived_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        $this->userRepository
            ->shouldReceive('deactivateDealer')
            ->with($dealerId)
            ->once();

        $this->inventoryRepository
            ->shouldReceive('archiveInventory')
            ->with(
                $dealerId,
                $inventoryParams
            )
            ->once();

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->once();

        $result = $service->deactivateDealer($dealerId);
        $this->assertTrue($result);
    }

    /**
     * @covers ::deactivateDealer
     *
     * @dataProvider invalidValueTypesDataProviderForDeactivateDealer
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testDeactivateDealerWithInvalidValueTypes($dealerId)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->deactivateDealer($dealerId);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForDeactivateDealer(): array
    {
        return [
            'Deactivate Dealer' => [
                'dealer_id' => 1001
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForDeactivateDealer(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate Parts with invalid dealer id' => [
                'dealer_id' => $dealerId
            ],
            'Deactivate Parts with invalid dealer id' => [
                'dealer_id' => $dealerId
            ]
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
