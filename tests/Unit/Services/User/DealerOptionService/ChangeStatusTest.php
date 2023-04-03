<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Models\User\User;
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
 * class ChangeStatusTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ChangeStatusTest extends TestCase
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);
    }

    /**
     * @covers ::changeStatus
     *
     * @dataProvider validDataProviderForChangeStatus
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testChangeStatus($dealerId, $status)
    {
        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once()

            ->shouldReceive('changeStatus')
            ->with(
                $dealerId,
                $status
            )
            ->once()

            ->shouldReceive('commitTransaction')
            ->once();

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->changeStatus($dealerId, $status);
        $this->assertTrue($result);
    }

    /**
     * @covers ::changeStatus
     *
     * @dataProvider invalidValueTypesDataProviderForChangeStatus
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testChangeStatusWithInvalidValueTypes($dealerId, $status)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->changeStatus($dealerId, $status);

        $this->assertTrue($result);
    }

    /**
     * @covers ::changeStatus
     *
     * @dataProvider invalidEmptyStatusProviderForChangeStatus
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testChangeStatusWithoutStatus($dealerId, $status)
    {
        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->userRepository
            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Status value is required to update dealer status.');

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->changeStatus($dealerId, $status);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForChangeStatus(): array
    {
        return [
            'Change Dealer Status' => [
                'dealer_id' => 1001,
                'status' => User::STATUSES[array_rand(User::STATUSES)]
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForChangeStatus(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Change Dealer Status with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'status' => User::STATUSES[array_rand(User::STATUSES)]
            ],
            'Change Dealer Status with with status' => [
                'dealer_id' => $dealerId,
                'status' => 200
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function invalidEmptyStatusProviderForChangeStatus(): array
    {
        return [
            'Change Dealer Status' => [
                'dealer_id' => 1001,
                'status' => ''
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
