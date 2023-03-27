<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Repositories\User\DealerPartRepositoryInterface;
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
 * class ManagePartsTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ManagePartsTest extends TestCase
{
    /**
     * @var DealerPartRepositoryInterface
     */
    private $dealerPartRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->dealerPartRepository = Mockery::mock(DealerPartRepositoryInterface::class);
        $this->app->instance(DealerPartRepositoryInterface::class, $this->dealerPartRepository);
    }

    /**
     * @covers ::manageParts
     *
     * @dataProvider validDataProviderForManageParts
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageParts($dealerId, $active)
    {
        $this->dealerPartRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $dealerPartsParams = [
            'dealer_id' => $dealerId,
            'since' => Carbon::now()->format('Y-m-d')
        ];

        if (!$active) {
            $this->dealerPartRepository
                ->shouldReceive('delete')
                ->with($dealerPartsParams)
                ->once();
        } else {
            $this->dealerPartRepository
                ->shouldReceive('create')
                ->with($dealerPartsParams)
                ->once();
        }

        $this->dealerPartRepository
            ->shouldReceive('commitTransaction')
            ->once();

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageParts($dealerId, $active);
        $this->assertTrue($result);
    }

    /**
     * @covers ::manageParts
     *
     * @dataProvider invalidValueTypesDataProviderForManageParts
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManagePartsWithInvalidValueTypes($dealerId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageParts($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageParts(): array
    {
        return [
            'Activate Parts' => [
                'dealer_id' => 1001,
                'active' => 1
            ],
            'Deactivate Parts' => [
                'dealer_id' => 1001,
                'active' => 0
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForManageParts(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate Parts with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'active' => 1
            ],
            'Deactivate Parts with invalid dealer id' => [
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
