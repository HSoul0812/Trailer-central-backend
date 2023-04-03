<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Models\User\User;
use App\Models\Website\Website;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\User\DealerOptionsService;
use Mockery;
use Tests\TestCase;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Support\Collection;

/**
 * Test for App\Services\User\DealerOptionsService
 *
 * class ManageMobileTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ManageMobileTest extends TestCase
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepository;

    private $user;
    private $website;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);

        $this->websiteConfigRepository = Mockery::mock(WebsiteConfigRepositoryInterface::class);
        $this->app->instance(WebsiteConfigRepositoryInterface::class, $this->websiteConfigRepository);

        $this->user = $this->getEloquentMock(User::class);
        $this->app->instance(User::class, $this->user);

        $this->website = $this->getEloquentMock(Website::class);
        $this->app->instance(Website::class, $this->website);
    }

    /**
     * @covers ::manageMobile
     *
     * @dataProvider validDataProviderForManageMobile
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageMobile($dealerId, $active)
    {
        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once()

            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($this->user);

        $this->initHasOneRelation($this->user, 'website', $this->website);

        $this->websiteConfigRepository
            ->shouldReceive('createOrUpdate')
            ->with(
                $this->website->id,
                [
                    'key' => 'general/mobile/enabled',
                    'value' => $active
                ]
            )
            ->once();

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->once();

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageMobile($dealerId, $active);
        $this->assertTrue($result);
    }

    /**
     * @covers ::manageMobile
     *
     * @dataProvider validDataProviderForManageMobile
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageMobileWithoutRelatedWebsite($dealerId, $active)
    {
        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once()

            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($this->user);

        // Faking website as null
        $this->user->website = null;

        if (is_null($this->user->website)) {
            Log::shouldReceive('error');
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('There\'s no website associated to this dealer.');
        }

        $this->userRepository
            ->shouldReceive('rollbackTransaction')
            ->once();

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageMobile($dealerId, $active);
        $this->assertTrue($result);
    }

    /**
     * @covers ::manageMobile
     *
     * @dataProvider invalidValueTypesDataProviderForManageMobile
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageMobileWithInvalidValueTypes($dealerId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageMobile($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageMobile(): array
    {
        return [
            'Activate Mobile' => [
                'dealer_id' => 1001,
                'active' => 1
            ],
            'Deactivate Mobile' => [
                'dealer_id' => 1001,
                'active' => 0
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForManageMobile(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate Mobile with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'active' => 1
            ],
            'Deactivate Mobile with invalid dealer id' => [
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
