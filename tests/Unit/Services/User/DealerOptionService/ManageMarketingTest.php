<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Models\User\DealerClapp;
use App\Repositories\Marketing\Craigslist\DealerRepositoryInterface;
use App\Services\User\DealerOptionsService;
use Mockery;
use Tests\TestCase;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Support\Collection;

/**
 * Test for App\Services\User\DealerOptionsService
 *
 * class ManageMarketingTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ManageMarketingTest extends TestCase
{
    private $dealer;

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = Mockery::mock(DealerRepositoryInterface::class);
        $this->app->instance(DealerRepositoryInterface::class, $this->dealer);
    }

    /**
     * @covers ::manageMarketing
     *
     * @dataProvider validDataProviderForManageMarketing
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageMarketing($dealerId, $active)
    {
        $dealerClapp = Mockery::mock(DealerClapp::class);

        $this->dealer
            ->shouldReceive('get')
            ->with([
                'dealer_id' => $dealerId
            ])
            ->once()
            ->andReturn($dealerClapp);

        if (!$active && !empty($dealerClapp)) {
            $dealerClapp
                ->shouldReceive('delete')
                ->once();
        }

        if ($active && empty($dealerClapp)) {
            $dealerClapp
                ->shouldReceive('create')
                ->with([
                    'dealer_id' => $dealerId,
                    'since' => Carbon::now()->format('Y-m-d')
                ])
                ->once();
        }

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageMarketing($dealerId, $active);
        $this->assertTrue($result);
    }

    /**
     * @covers ::manageMarketing
     *
     * @dataProvider invalidValueTypesDataProviderForManageMarketing
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageMarketingWithInvalidValueTypes($dealerId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageMarketing($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageMarketing(): array
    {
        return [
            'Activate Marketing' => [
                'dealer_id' => 1001,
                'active' => 1
            ],
            'Deactivate Marketing' => [
                'dealer_id' => 1001,
                'active' => 0
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForManageMarketing(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate Marketing with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'active' => 1
            ],
            'Deactivate Marketing with invalid dealer id' => [
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
