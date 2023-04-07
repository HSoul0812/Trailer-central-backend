<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Models\User\User;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\DealerOptionsService;
use Exception;
use Mockery;
use Tests\TestCase;
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
class ToggleDealerActiveStatusTest extends TestCase
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
     * @dataProvider validDataProviderForToggleDealerActiveStatus
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws Exception
     */
    public function testDeactivateDealer($dealerId, $params)
    {
        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);

        $deletedAt = User::find($dealerId)->deleted_at;
        $datetime = Carbon::now()->format('Y-m-d H:i:s');

        $inventoryParams = [
            'dealer_id' => $dealerId,
            'active' => $params['active'],
            'is_archived' => $params['is_archived'],
            'archived_at' => $params['active'] ? null : $datetime
        ];

        $this->userRepository
            ->shouldReceive('toggleDealerStatus')
            ->with(
                $dealerId,
                $params['active'],
                $datetime
            )
            ->once();

        $this->inventoryRepository
            ->shouldReceive('massUpdateDealerInventoryOnActiveStateChange')
            ->with(
                $dealerId,
                $inventoryParams,
                $deletedAt
            )
            ->once();

        $result = $service->toggleDealerActiveStatus($dealerId, $params['active']);
        $this->assertTrue($result);
    }

    /**
     * @covers ::deactivateDealer
     *
     * @dataProvider invalidValueTypesDataProviderForToggleDealerActiveStatus
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws Exception
     */
    public function testDeactivateDealerWithInvalidValueTypes($dealerId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->toggleDealerActiveStatus($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForToggleDealerActiveStatus(): array
    {
        return [
            'Deactivate Dealer' => [
                'dealer_id' => 1004,
                'params' => [
                    'active' => 0,
                    'is_archived' => 1
                ]
            ],
            'Activate Dealer' => [
                'dealer_id' => 1004,
                'params' => [
                    'active' => 1,
                    'is_archived' => 0
                ]
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForToggleDealerActiveStatus(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'active' => 1
            ],
            'Deactivate with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'active' => 0
            ]
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
