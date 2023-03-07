<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Models\User\User;
use App\Models\Website\Website;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface;
use App\Services\User\DealerOptionsService;
use App\Services\User\DealerOptionsServiceInterface;
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
 * class ManageEcommerceTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ManageEcommerceTest extends TestCase
{

    /**
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $websiteEntityRepository;

    /**
     * @var DealerOptionsServiceInterface
     */
    private $dealerOptionService;

    private $user;
    private $website;

    public function setUp(): void
    {
        parent::setUp();

        $this->dealerOptionService = Mockery::mock(DealerOptionsServiceInterface::class);
        $this->app->instance(DealerOptionsServiceInterface::class, $this->dealerOptionService);

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);

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
     * @covers ::manageEcommerce
     *
     * @dataProvider validDataProviderForManageEcommerce
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageEcommerce($dealerId, $active)
    {
        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $collection = new Collection();

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once()

            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($this->user);

        $this->initHasOneRelation($this->user, 'website', $this->website);

        $websiteConfigParams = [
            'website_id' => $this->website->id,
            'key' => $service::ECOMMERCE_KEY_ENABLE
        ];

        $this->websiteConfigRepository
            ->shouldReceive('getAll')
            ->with($websiteConfigParams)
            ->once()
            ->andReturn($collection);

        foreach ($collection as $websiteConfig) {
            $this->websiteConfigRepository
                ->shouldReceive('delete')
                ->with([
                    'id' => $websiteConfig->id
                ])
                ->zeroOrMoreTimes();
        }

        if (!$active) {
            $this->websiteEntityRepository
                ->shouldReceive('update')
                ->with([
                    'entity_type' => $service::TEXTRAIL_PARTS_ENTITY_TYPE,
                    'website_id' => $this->website->id,
                    'is_active' => $active,
                    'in_nav' => 0
                ]);
        } else {
            $newWebsiteConfigActiveParams = [
                'website_id' => $this->website->id,
                'key' => $service::ECOMMERCE_KEY_ENABLE,
                'value' => 1
            ];

            if (!$service->isAllowedParts($dealerId)) {
                $this->dealerOptionService
                    ->shouldReceive('manageParts')
                    ->with([
                        'dealerId' => $dealerId,
                        'active' => $active
                    ])
                    ->once();
            }

            $this->websiteConfigRepository
                ->shouldReceive('create')
                ->with($newWebsiteConfigActiveParams)
                ->once();

            $this->websiteEntityRepository
                ->shouldReceive('update')
                ->with([
                    'entity_type' => $service::TEXTRAIL_PARTS_ENTITY_TYPE,
                    'website_id' => $this->website->id,
                    'entity_view' => 'textrail-parts-list',
                    'template' => '2columns-left',
                    'parent' => 0,
                    'title' => 'Parts Direct Shipping',
                    'url_path' => 'parts-direct-shipping',
                    'meta_keywords' => 'trailer, parts, shipping, order, cart, ship, direct',
                    'meta_description' => 'Trailer parts can be added to your cart, ordered, and shipped directly to your door!',
                    'url_path_external' => 0,
                    'in_nav' => 0,
                    'is_active' => $active,
                    'deleted' => 0
                ])
                ->once();
        }

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->once();

        $result = $service->manageECommerce($dealerId, $active);
        $this->assertTrue($result);
    }

    /**
     * @covers ::manageEcommerce
     *
     * @dataProvider validDataProviderForManageEcommerce
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageEcommerceWithoutRelatedWebsite($dealerId, $active)
    {
        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);

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

        $result = $service->manageECommerce($dealerId, $active);
        $this->assertTrue($result);
    }

    /**
     * @covers ::manageEcommerce
     *
     * @dataProvider invalidValueTypesDataProviderForManageEcommerce
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageEcommerceWithInvalidValueTypes($dealerId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCdk($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageEcommerce(): array
    {
        return [
            'Activate Ecommerce' => [
                'dealer_id' => 1001,
                'active' => 1
            ],
            'Deactivate Ecommerce' => [
                'dealer_id' => 1001,
                'active' => 0
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForManageEcommerce(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate Ecommerce with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'active' => 1
            ],
            'Deactivate Ecommerce with invalid dealer id' => [
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
