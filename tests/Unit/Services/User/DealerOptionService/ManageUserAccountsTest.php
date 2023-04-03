<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Models\User\User;
use App\Models\Website\Website;
use App\Repositories\GenericRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use App\Services\User\DealerOptionsService;
use Mockery;
use Tests\TestCase;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Database\Eloquent\Collection;

/**
 * Test for App\Services\User\DealerOptionsService
 *
 * class ManageUserAccountsTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ManageUserAccountsTest extends TestCase
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $websiteEntityRepository;

    private $user;
    private $website;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);

        $this->websiteRepository = Mockery::mock(WebsiteRepositoryInterface::class);
        $this->app->instance(WebsiteRepositoryInterface::class, $this->websiteRepository);

        $this->websiteConfigRepository = Mockery::mock(WebsiteConfigRepositoryInterface::class);
        $this->app->instance(WebsiteConfigRepositoryInterface::class, $this->websiteConfigRepository);

        $this->websiteEntityRepository = Mockery::mock(EntityRepositoryInterface::class);
        $this->app->instance(EntityRepositoryInterface::class, $this->websiteEntityRepository);

        $this->user = $this->getEloquentMock(User::class);
        $this->app->instance(User::class, $this->user);

        $this->website = $this->getEloquentMock(Website::class);
        $this->app->instance(Website::class, $this->website);
    }

    /**
     * @covers ::manageUserAccounts
     *
     * @dataProvider validDataProviderForManageUserAccounts
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageUserAccounts($dealerId, $active)
    {
        $this->website->id = 1000;
        $websites = new Collection([$this->website]);

        $this->websiteRepository
            ->shouldReceive('getAll')
            ->with([
                GenericRepository::CONDITION_AND_WHERE => [
                    ['dealer_id', '=', $dealerId]
                ]
            ], false)
            ->once()
            ->andReturn($websites);

        $this->website
            ->shouldReceive('getKey')
            ->atLeast()
            ->times(4)
            ->andReturn($this->website->id);

        foreach ($websites as $website) {
            $this->websiteConfigRepository
                ->shouldReceive('setValue')
                ->with(
                    $website->getKey(),
                    'general/user_accounts',
                    $active
                )
                ->once();

            if (!$active) {
                $this->websiteEntityRepository
                    ->shouldReceive('delete')
                    ->with([
                        'entity_type' => '41',
                        'website_id' => $website->getKey()
                    ])
                    ->once()

                    ->shouldReceive('delete')
                    ->with([
                        'entity_type' => '42',
                        'website_id' => $website->getKey()
                    ])
                    ->once()

                    ->shouldReceive('delete')
                    ->with([
                        'entity_type' => '43',
                        'website_id' => $website->getKey()
                    ])
                    ->once()

                    ->shouldReceive('delete')
                    ->with([
                        'entity_type' => '44',
                        'website_id' => $website->getKey()
                    ])
                    ->once();
            } else {
                $this->websiteEntityRepository
                    ->shouldReceive('update')
                    ->with([
                        'entity_type' => '41',
                        'website_id' => $website->getKey(),
                        'entity_view' => 'login',
                        'template' => '1column',
                        'parent' => 0,
                        'title' => 'Login',
                        'url_path' => 'login',
                        'url_path_external' => 0,
                        'sort_order' => 85,
                        'in_nav' => 1,
                        'is_active' => 1,
                        'deleted' => 0
                    ])
                    ->once()

                    ->shouldReceive('update')
                    ->with([
                        'entity_type' => '42',
                        'website_id' => $website->getKey(),
                        'entity_view' => 'signup',
                        'template' => '1column',
                        'parent' => 0,
                        'title' => 'SignUp',
                        'url_path' => 'signup',
                        'url_path_external' => 0,
                        'in_nav' => 0,
                        'is_active' => 1,
                        'deleted' => 0
                    ])
                    ->once()

                    ->shouldReceive('update')
                    ->with([
                        'entity_type' => '43',
                        'website_id' => $website->getKey(),
                        'entity_view' => 'account',
                        'template' => '1column',
                        'parent' => 0,
                        'title' => 'Account Information',
                        'url_path' => 'account',
                        'url_path_external' => 0,
                        'in_nav' => 0,
                        'is_active' => 1,
                        'deleted' => 0
                    ])
                    ->once()

                    ->shouldReceive('update')
                    ->with([
                        'entity_type' => '44',
                        'website_id' => $website->getKey(),
                        'entity_view' => 'inventory-list-hybrid',
                        'template' => '1column',
                        'parent' => 0,
                        'title' => 'Favorite Inventories',
                        'url_path' => 'favorite-inventories',
                        'url_path_external' => 0,
                        'in_nav' => 0,
                        'is_active' => 1,
                        'deleted' => 0
                    ])
                    ->once();
            }
        }

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageUserAccounts($dealerId, $active);
        $this->assertTrue($result);
    }

    /**
     * @covers ::manageUserAccounts
     *
     * @dataProvider validDataProviderForManageUserAccounts
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageUserAccountsWithoutRelatedWebsites($dealerId, $active)
    {
        $this->websiteRepository
            ->shouldReceive('getAll')
            ->with([
                GenericRepository::CONDITION_AND_WHERE => [
                    ['dealer_id', '=', $dealerId]
                ]
            ], false)
            ->once()
            ->andReturn([]);

        Log::shouldReceive('error');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There\'s no website associated to this dealer');

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageUserAccounts($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @covers ::manageUserAccounts
     *
     * @dataProvider invalidValueTypesDataProviderForManageUserAccounts
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageUserAccountsWithInvalidValueTypes($dealerId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageUserAccounts($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageUserAccounts(): array
    {
        return [
            'Activate UserAccounts' => [
                'dealer_id' => 1001,
                'active' => 1
            ],
            'Deactivate UserAccounts' => [
                'dealer_id' => 1001,
                'active' => 0
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForManageUserAccounts(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate UserAccounts with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'active' => 1
            ],
            'Deactivate UserAccounts with invalid dealer id' => [
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
