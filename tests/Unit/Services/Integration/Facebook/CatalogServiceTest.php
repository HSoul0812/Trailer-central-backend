<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Models\CRM\User\User;
use App\Models\User\DealerLocation;
use App\Models\Integration\Auth\AccessToken;
use App\Models\Integration\Facebook\Catalog;
use App\Models\Integration\Facebook\Feed;
use App\Models\Integration\Facebook\Page;
use App\Jobs\Integration\Facebook\Catalog\VehicleJob;
use App\Jobs\Integration\Facebook\Catalog\HomeJob;
use App\Jobs\Integration\Facebook\Catalog\ProductJob;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Integration\Facebook\FeedRepositoryInterface;
use App\Services\Integration\Facebook\CatalogService;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Services\Integration\Facebook\CatalogService
 *
 * Class CatalogServiceTest
 * @package Tests\Unit\Services\Integration\Facebook
 *
 * @coversDefaultClass \App\Services\Inventory\CatalogService
 */
class CatalogServiceTest extends TestCase
{
    /**
     * @const Catalog Scopes
     */
    const TEST_EXPIRES_IN = 60 * 60 * 24 * 60;

    /**
     * @const string
     */
    const TEST_INQUIRY_EMAIL = 'admin@operatebeyond.com';
    const TEST_INQUIRY_NAME = 'Operate Beyond';

    /**
     * @const string
     */
    const FB_AUTH_TYPE = 'marketing';


    /**
     * @var LegacyMockInterface|BusinessServiceInterface
     */
    private $businessServiceMock;

    /**
     * @var LegacyMockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var LegacyMockInterface|PageRepositoryInterface
     */
    private $pageRepositoryMock;

    /**
     * @var LegacyMockInterface|CatalogRepositoryInterface
     */
    private $catalogRepositoryMock;

    /**
     * @var LegacyMockInterface|FeedRepositoryInterface
     */
    private $feedRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->businessServiceMock = Mockery::mock(BusinessServiceInterface::class);
        $this->app->instance(BusinessServiceInterface::class, $this->businessServiceMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);

        $this->pageRepositoryMock = Mockery::mock(PageRepositoryInterface::class);
        $this->app->instance(PageRepositoryInterface::class, $this->pageRepositoryMock);

        $this->catalogRepositoryMock = Mockery::mock(CatalogRepositoryInterface::class);
        $this->app->instance(CatalogRepositoryInterface::class, $this->catalogRepositoryMock);

        $this->feedRepositoryMock = Mockery::mock(FeedRepositoryInterface::class);
        $this->app->instance(FeedRepositoryInterface::class, $this->feedRepositoryMock);
    }

    /**
     * @group Marketing
     * @covers ::show
     *
     * @throws BindingResolutionException
     */
    public function testShow()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->id = 1;
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);


        // Create Validation Response
        $validate = ['is_valid' => true, 'is_expired' => false];

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $catalog->id])
            ->andReturn($catalog);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validate);

        // Validate Show Catalog Result
        $result = $service->show(['id' => $catalog->id]);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalog->id);
    }

    /**
     * @group Marketing
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->id = 1;
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);


        // Create Validation Response
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Create Request Params
        $createRequestParams = [
            'dealer_id' => $catalog->dealer_id,
            'dealer_location_id' => $catalog->dealer_location_id,
            'access_token' => $catalog->accessToken->access_token,
            'id_token' => $catalog->accessToken->id_token,
            'expires_in' => $catalog->accessToken->expires_in,
            'expires_at' => $catalog->accessToken->expires_at,
            'issued_at' => $catalog->accessToken->issued_at,
            'business_id' => $catalog->business_id,
            'catalog_id' => $catalog->catalog_id,
            'account_name' => $catalog->account_name,
            'account_id' => $catalog->account_id,
            'page_title' => $catalog->page->title,
            'page_id' => $catalog->page->page_id,
            'feed_id' => $catalog->feed_id,
            'filters' => '',
            'is_active' => 1
        ];

        // Create Catalog Params
        $createCatalogParams = $createRequestParams;
        $createCatalogParams['fbapp_page_id'] = $catalog->page->id;

        // Create Auth Params
        $refreshAuthParams = $createCatalogParams;
        $refreshAuthParams['token_type'] = 'facebook';
        $refreshAuthParams['relation_type'] = 'fbapp_catalog';
        $refreshAuthParams['relation_id'] = $catalog->id;

        // Create Auth Params
        $createAuthParams = $refreshAuthParams;
        $createAuthParams['refresh_token'] = $catalog->accessToken->refresh_token;

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Create Page
        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createRequestParams)
            ->andReturn($page);

        // Mock Create Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createCatalogParams)
            ->andReturn($catalog);

        // Mock Get FB Refresh Token
        $this->businessServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->with($refreshAuthParams)
            ->andReturn([
                'access_token' => $accessToken->refresh_token
            ]);

        // Mock Create Catalog Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createAuthParams)
            ->andReturn($accessToken);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($accessToken)
            ->andReturn($validate);

        // Validate Create Catalog Result
        $result = $service->create($createRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalog->id);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @group Marketing
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreatePage()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock PageToken
        $pageToken = $this->getEloquentMock(AccessToken::class);
        $pageToken->id = 1;
        $pageToken->dealer_id = $dealer->dealer_id;
        $pageToken->token_type = 'facebook';
        $pageToken->relation_type = 'fbapp_page';
        $pageToken->relation_id = 1;
        $pageToken->access_token = 1;
        $pageToken->refresh_token = 1;
        $pageToken->id_token = 1;
        $pageToken->expires_in = self::TEST_EXPIRES_IN;
        $pageToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $pageToken->issued_at = date("Y-m-d H:i:s", $time);
        $pageToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->id = 1;
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);


        // Create Validation Response
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Create Request Params
        $createRequestParams = [
            'dealer_id' => $catalog->dealer_id,
            'dealer_location_id' => $catalog->dealer_location_id,
            'access_token' => $catalog->accessToken->access_token,
            'id_token' => $catalog->accessToken->id_token,
            'expires_in' => $catalog->accessToken->expires_in,
            'expires_at' => $catalog->accessToken->expires_at,
            'issued_at' => $catalog->accessToken->issued_at,
            'page_token' => $pageToken->access_token,
            'business_id' => $catalog->business_id,
            'catalog_id' => $catalog->catalog_id,
            'account_name' => $catalog->account_name,
            'account_id' => $catalog->account_id,
            'page_title' => $catalog->page->title,
            'page_id' => $catalog->page->page_id,
            'feed_id' => $catalog->feed_id,
            'filters' => '',
            'is_active' => 1
        ];

        // Create Catalog Params
        $createCatalogParams = $createRequestParams;
        $createCatalogParams['fbapp_page_id'] = $catalog->page->id;

        // Create Auth Params
        $refreshAuthParams = $createCatalogParams;
        $refreshAuthParams['token_type'] = 'facebook';
        $refreshAuthParams['relation_type'] = 'fbapp_catalog';
        $refreshAuthParams['relation_id'] = $catalog->id;

        // Create Auth Params
        $createAuthParams = $refreshAuthParams;
        $createAuthParams['refresh_token'] = $catalog->accessToken->refresh_token;

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Create Page
        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createRequestParams)
            ->andReturn($page);

        // Mock Create Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createCatalogParams)
            ->andReturn($catalog);

        // Mock Get FB Refresh Token
        $this->businessServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->andReturn([
                'access_token' => $accessToken->refresh_token
            ]);

        // Mock Create Catalog Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createAuthParams)
            ->andReturn($accessToken);

        // Mock Get FB Refresh Token
        $this->businessServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->with(Mockery::on(function ($params) use ($pageToken) {
                return $params['relation_type'] === $pageToken->relation_type &&
                       $params['relation_id'] === $pageToken->relation_id;
            }))
            ->andReturn($pageToken->refresh_token);

        // Mock Create Catalog Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($params) use ($pageToken) {
                return $params['relation_type'] === $pageToken->relation_type &&
                       $params['relation_id'] === $pageToken->relation_id;
            }))
            ->andReturn($pageToken);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with(Mockery::on(function ($token) use($accessToken) {
                return $token->access_token === $accessToken->access_token &&
                       $token->relation_type === $accessToken->relation_type &&
                       $token->relation_id === $accessToken->relation_id;
            }))
            ->andReturn($validate);

        // Validate Create Catalog Result
        $result = $service->create($createRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalog->id);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }


    /**
     * @group Marketing
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdate()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->id = 1;
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);


        // Create Validation Response
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Update Request Params
        $updateRequestParams = [
            'id' => $catalog->id,
            'dealer_id' => $catalog->dealer_id,
            'dealer_location_id' => $catalog->dealer_location_id,
            'access_token' => $catalog->accessToken->access_token,
            'id_token' => $catalog->accessToken->id_token,
            'refresh_token' => $catalog->accessToken->refresh_token,
            'expires_in' => $catalog->accessToken->expires_in,
            'expires_at' => $catalog->accessToken->expires_at,
            'issued_at' => $catalog->accessToken->issued_at,
            'business_id' => $catalog->business_id,
            'catalog_id' => $catalog->catalog_id,
            'account_name' => $catalog->account_name,
            'account_id' => $catalog->account_id,
            'page_title' => $catalog->page->title,
            'page_id' => $catalog->page->page_id,
            'feed_id' => $catalog->feed_id,
            'filters' => '',
            'is_active' => 1
        ];

        // Relation Auth Params
        $relationAuthParams = $updateRequestParams;
        unset($relationAuthParams['id']);
        $relationAuthParams['token_type'] = 'facebook';
        $relationAuthParams['relation_type'] = 'fbapp_catalog';
        $relationAuthParams['relation_id'] = $catalog->id;

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Update Page
        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($updateRequestParams)
            ->andReturn($page);

        // Mock Update Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($updateRequestParams)
            ->andReturn($catalog);

        // Mock Get Relation Token
        $this->tokenRepositoryMock
            ->shouldReceive('getRelation')
            ->once()
            ->with($relationAuthParams)
            ->andReturn($accessToken);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($catalog->accessToken)
            ->andReturn($validate);

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalog->id);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @group Marketing
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdatePage()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock PageToken
        $pageToken = $this->getEloquentMock(AccessToken::class);
        $pageToken->id = 1;
        $pageToken->dealer_id = $dealer->dealer_id;
        $pageToken->token_type = 'facebook';
        $pageToken->relation_type = 'fbapp_page';
        $pageToken->relation_id = 1;
        $pageToken->access_token = 1;
        $pageToken->refresh_token = 1;
        $pageToken->id_token = 1;
        $pageToken->expires_in = self::TEST_EXPIRES_IN;
        $pageToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $pageToken->issued_at = date("Y-m-d H:i:s", $time);
        $pageToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $pageToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->id = 1;
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);


        // Create Validation Response
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Update Request Params
        $updateRequestParams = [
            'id' => $catalog->id,
            'dealer_id' => $catalog->dealer_id,
            'dealer_location_id' => $catalog->dealer_location_id,
            'access_token' => $catalog->accessToken->access_token,
            'id_token' => $catalog->accessToken->id_token,
            'refresh_token' => $catalog->accessToken->refresh_token,
            'expires_in' => $catalog->accessToken->expires_in,
            'expires_at' => $catalog->accessToken->expires_at,
            'issued_at' => $catalog->accessToken->issued_at,
            'page_token' => $pageToken->access_token,
            'business_id' => $catalog->business_id,
            'catalog_id' => $catalog->catalog_id,
            'account_name' => $catalog->account_name,
            'account_id' => $catalog->account_id,
            'page_title' => $catalog->page->title,
            'page_id' => $catalog->page->page_id,
            'feed_id' => $catalog->feed_id,
            'filters' => '',
            'is_active' => 1
        ];

        // Relation Auth Params
        $relationAuthParams = $updateRequestParams;
        unset($relationAuthParams['id']);
        $relationAuthParams['token_type'] = 'facebook';
        $relationAuthParams['relation_type'] = 'fbapp_catalog';
        $relationAuthParams['relation_id'] = $catalog->id;

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Update Page
        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($updateRequestParams)
            ->andReturn($page);

        // Mock Update Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($updateRequestParams)
            ->andReturn($catalog);

        // Mock Get Relation Token
        $this->tokenRepositoryMock
            ->shouldReceive('getRelation')
            ->once()
            ->with($relationAuthParams)
            ->andReturn($accessToken);

        // Mock Get FB Refresh Token
        $this->businessServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->with(Mockery::on(function ($params) use ($pageToken) {
                return $params['relation_type'] === $pageToken->relation_type &&
                       $params['relation_id'] === $pageToken->relation_id;
            }))
            ->andReturn($pageToken->refresh_token);

        // Mock Create Catalog Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($params) use ($pageToken) {
                return $params['relation_type'] === $pageToken->relation_type &&
                       $params['relation_id'] === $pageToken->relation_id;
            }))
            ->andReturn($pageToken);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with(Mockery::on(function ($token) use($accessToken) {
                return $token->access_token === $accessToken->access_token &&
                       $token->relation_type === $accessToken->relation_type &&
                       $token->relation_id === $accessToken->relation_id;
            }))
            ->andReturn($validate);

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalog->id);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }


    /**
     * @group Marketing
     * @covers ::delete
     *
     * @throws BindingResolutionException
     */
    public function testDelete()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->id = 1;
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);


        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $catalog->id])
            ->andReturn($catalog);

        // Mock Delete Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with([
                'token_type' => $accessToken->token_type,
                'relation_type' => $accessToken->relation_type,
                'relation_id' => $accessToken->relation_id
            ]);

        // Mock Delete Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($catalog->id)
            ->andReturn(true);

        // Validate Delete Catalog Result
        $result = $service->delete($catalog->id);

        // Assert Match
        $this->assertTrue($result);
    }

    /**
     * @group Marketing
     * @covers ::payload
     *
     * @throws BindingResolutionException
     */
    public function testPayload()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->dealer_location_id = 1;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->feed = null;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);

        // Mock Feed
        $feed = $this->getEloquentMock(Feed::class);
        $feed->business_id = $catalog->business_id;
        $feed->catalog_id = $catalog->catalog_id;
        $feed->feed_id = 1;
        $feed->feed_title = 'Feed for Catalog #' . $catalog->catalog_id;
        $feed->feed_url = '/' . Feed::CATALOG_URL_PREFIX . '/' . $feed->business_id . '/' . $feed->catalog_id . '.csv';
        $feed->is_active = 1;
        $feedUrl = config('filesystems.disks.s3.url') . $feed->feed_url;


        // Get Pre-Created Payload
        $payload = $this->getTestPayload($catalog);

        // Parse Payload Data
        $integrations = (array) json_decode($payload);


        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('findOne')
            ->once()
            ->with(['catalog_id' => $catalog->catalog_id])
            ->andReturn($catalog);

        // Mock Get Feed URL
        $this->feedRepositoryMock
            ->shouldReceive('getFeedUrl')
            ->once()
            ->with($catalog->business_id, $catalog->catalog_id)
            ->andReturn($feedUrl);

        // Mock Get Feed Name
        $this->feedRepositoryMock
            ->shouldReceive('getFeedName')
            ->twice()
            ->with($catalog->catalog_id)
            ->andReturn($feed->feed_title);

        // Mock Schedule Feed
        $this->businessServiceMock
            ->shouldReceive('scheduleFeed')
            ->once()
            ->andReturn(['id' => $feed->feed_id]);

        // Mock Get Second Feed URL
        $this->feedRepositoryMock
            ->shouldReceive('getFeedUrl')
            ->once()
            ->with($catalog->business_id, $catalog->catalog_id, false)
            ->andReturn($feedUrl);

        // Mock Create or Update Feed
        $this->feedRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->andReturn($feed);

        // Expect Vehicle Job
        $this->expectsJobs(VehicleJob::class);

        // Handle Catalog Payload Result
        $result = $service->payload($payload);

        $this->assertSame($result, [
            'success' => true,
            'feeds' => count($integrations)
        ]);
    }

    /**
     * @group Marketing
     * @covers ::payload
     *
     * @throws BindingResolutionException
     */
    public function testPayloadHome()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->dealer_location_id = 1;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->feed = null;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);

        // Mock Feed
        $feed = $this->getEloquentMock(Feed::class);
        $feed->business_id = $catalog->business_id;
        $feed->catalog_id = $catalog->catalog_id;
        $feed->feed_id = 1;
        $feed->feed_title = 'Feed for Catalog #' . $catalog->catalog_id;
        $feed->feed_url = '/' . Feed::CATALOG_URL_PREFIX . '/' . $feed->business_id . '/' . $feed->catalog_id . '.csv';
        $feed->is_active = 1;
        $feedUrl = config('filesystems.disks.s3.url') . $feed->feed_url;


        // Get Pre-Created Payload
        $payload = $this->getTestPayload($catalog, Catalog::HOME_TYPE);

        // Parse Payload Data
        $integrations = (array) json_decode($payload);


        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('findOne')
            ->once()
            ->with(['catalog_id' => $catalog->catalog_id])
            ->andReturn($catalog);

        // Mock Get Feed URL
        $this->feedRepositoryMock
            ->shouldReceive('getFeedUrl')
            ->once()
            ->with($catalog->business_id, $catalog->catalog_id)
            ->andReturn($feedUrl);

        // Mock Get Feed Name
        $this->feedRepositoryMock
            ->shouldReceive('getFeedName')
            ->twice()
            ->with($catalog->catalog_id)
            ->andReturn($feed->feed_title);

        // Mock Schedule Feed
        $this->businessServiceMock
            ->shouldReceive('scheduleFeed')
            ->once()
            ->andReturn(['id' => $feed->feed_id]);

        // Mock Get Second Feed URL
        $this->feedRepositoryMock
            ->shouldReceive('getFeedUrl')
            ->once()
            ->with($catalog->business_id, $catalog->catalog_id, false)
            ->andReturn($feedUrl);

        // Mock Create or Update Feed
        $this->feedRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->andReturn($feed);

        // Expect Home Job
        $this->expectsJobs(HomeJob::class);

        // Handle Catalog Payload Result
        $result = $service->payload($payload);

        $this->assertSame($result, [
            'success' => true,
            'feeds' => count($integrations)
        ]);
    }

    /**
     * @group Marketing
     * @covers ::payload
     *
     * @throws BindingResolutionException
     */
    public function testPayloadProduct()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->dealer_location_id = 1;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->feed = null;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);

        // Mock Feed
        $feed = $this->getEloquentMock(Feed::class);
        $feed->business_id = $catalog->business_id;
        $feed->catalog_id = $catalog->catalog_id;
        $feed->feed_id = 1;
        $feed->feed_title = 'Feed for Catalog #' . $catalog->catalog_id;
        $feed->feed_url = '/' . Feed::CATALOG_URL_PREFIX . '/' . $feed->business_id . '/' . $feed->catalog_id . '.csv';
        $feed->is_active = 1;
        $feedUrl = config('filesystems.disks.s3.url') . $feed->feed_url;


        // Get Pre-Created Payload
        $payload = $this->getTestPayload($catalog, Catalog::PRODUCT_TYPE);

        // Parse Payload Data
        $integrations = (array) json_decode($payload);


        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('findOne')
            ->once()
            ->with(['catalog_id' => $catalog->catalog_id])
            ->andReturn($catalog);

        // Mock Get Feed URL
        $this->feedRepositoryMock
            ->shouldReceive('getFeedUrl')
            ->once()
            ->with($catalog->business_id, $catalog->catalog_id)
            ->andReturn($feedUrl);

        // Mock Get Feed Name
        $this->feedRepositoryMock
            ->shouldReceive('getFeedName')
            ->twice()
            ->with($catalog->catalog_id)
            ->andReturn($feed->feed_title);

        // Mock Schedule Feed
        $this->businessServiceMock
            ->shouldReceive('scheduleFeed')
            ->once()
            ->andReturn(['id' => $feed->feed_id]);

        // Mock Get Second Feed URL
        $this->feedRepositoryMock
            ->shouldReceive('getFeedUrl')
            ->once()
            ->with($catalog->business_id, $catalog->catalog_id, false)
            ->andReturn($feedUrl);

        // Mock Create or Update Feed
        $this->feedRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->andReturn($feed);

        // Expect Product Job
        $this->expectsJobs(ProductJob::class);

        // Handle Catalog Payload Result
        $result = $service->payload($payload);

        $this->assertSame($result, [
            'success' => true,
            'feeds' => count($integrations)
        ]);
    }

    /**
     * @group Marketing
     * @covers ::payload
     *
     * @throws BindingResolutionException
     */
    public function testPayloadUpdate()
    {
        // Set Defaults
        $time = time();
        $scopes = explode(' ', config('oauth.fb.' . self::FB_AUTH_TYPE . '.scopes'));

        // Mock Location
        $location = $this->getEloquentMock(DealerLocation::class);
        $location->dealer_location_id = 1;
        $location->dealer_id = 1;
        $location->name = 'Indianopolis';
        $location->salesTax = null;
        $location->shouldReceive('inventoryCount')->andReturn(0);
        $location->shouldReceive('referenceCount')->andReturn(0);

        // Mock User
        $dealer = $this->getEloquentMock(User::class);
        $dealer->dealer_id = 1;
        $dealer->email = self::TEST_INQUIRY_EMAIL;
        $dealer->name = self::TEST_INQUIRY_NAME;

        // Mock AccessToken
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->id = 1;
        $accessToken->dealer_id = $dealer->dealer_id;
        $accessToken->token_type = 'facebook';
        $accessToken->relation_type = 'fbapp_catalog';
        $accessToken->relation_id = 1;
        $accessToken->access_token = 1;
        $accessToken->refresh_token = 1;
        $accessToken->id_token = 1;
        $accessToken->expires_in = self::TEST_EXPIRES_IN;
        $accessToken->expires_at = date("Y-m-d H:i:s", $time + self::TEST_EXPIRES_IN);
        $accessToken->issued_at = date("Y-m-d H:i:s", $time);
        $accessToken->shouldReceive('getScopeAttribute')->andReturn($scopes);

        // Mock Page
        $page = $this->getEloquentMock(Page::class);
        $page->id = 1;
        $page->dealer_id = $dealer->dealer_id;
        $page->page_id = 1;
        $page->title = $dealer->name;
        $page->user = $dealer;
        $page->accessToken = $accessToken;

        // Mock Catalog
        $catalog = $this->getEloquentMock(Catalog::class);
        $catalog->dealer_id = $dealer->dealer_id;
        $catalog->dealer_location_id = 1;
        $catalog->fbapp_page_id = $page->id;
        $catalog->business_id = 1;
        $catalog->catalog_id = 1;
        $catalog->catalog_name = $dealer->name;
        $catalog->catalog_type = Catalog::VEHICLE_TYPE;
        $catalog->account_name = $dealer->name;
        $catalog->account_id = 1;
        $catalog->is_active = 1;
        $catalog->accessToken = $accessToken;
        $catalog->page = $page;
        $catalog->user = $dealer;
        $catalog->dealerLocation = $location;
        $catalog->shouldReceive('getCatalogNameIdAttribute')
                ->andReturn($catalog->catalog_name);

        // Mock Feed
        $feed = $this->getEloquentMock(Feed::class);
        $feed->business_id = $catalog->business_id;
        $feed->catalog_id = $catalog->catalog_id;
        $feed->feed_id = 1;
        $feed->feed_title = 'Feed for Catalog #' . $catalog->catalog_id;
        $feed->feed_url = '/' . Feed::CATALOG_URL_PREFIX . '/' . $feed->business_id . '/' . $feed->catalog_id . '.csv';
        $feed->is_active = 1;
        $feedUrl = config('filesystems.disks.s3.url') . $feed->feed_url;
        $catalog->feed_id = $feed->feed_id;
        $catalog->feed = $feed;


        // Get Pre-Created Payload
        $payload = $this->getTestPayload($catalog);

        // Parse Payload Data
        $integrations = (array) json_decode($payload);


        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('findOne')
            ->once()
            ->with(['catalog_id' => $catalog->catalog_id])
            ->andReturn($catalog);

        // Mock Schedule Feed
        $this->businessServiceMock
            ->shouldReceive('validateFeed')
            ->once()
            ->andReturn(['id' => $feed->feed_id]);

        // Mock Schedule Feed
        $this->businessServiceMock
            ->shouldReceive('scheduleFeed')
            ->never()
            ->andReturn(['id' => $feed->feed_id]);

        // Mock Get Second Feed URL
        $this->feedRepositoryMock
            ->shouldReceive('getFeedUrl')
            ->once()
            ->with($catalog->business_id, $catalog->catalog_id, false)
            ->andReturn($feedUrl);

        // Mock Get Feed Name
        $this->feedRepositoryMock
            ->shouldReceive('getFeedName')
            ->once()
            ->with($catalog->catalog_id)
            ->andReturn($feed->feed_title);

        // Mock Create or Update Feed
        $this->feedRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->andReturn($feed);

        // Expect Vehicle Job
        $this->expectsJobs(VehicleJob::class);

        // Handle Catalog Payload Result
        $result = $service->payload($payload);

        $this->assertSame($result, [
            'success' => true,
            'feeds' => count($integrations)
        ]);
    }


    /**
     * Get Test Payload
     * 
     * @return string
     */
    private function getTestPayload($catalog, $catalogType = '') {
        // Get Preset Vars
        $catalogId = $catalog->catalog_id;
        $accountId = $catalog->account_id;
        $businessId = $catalog->business_id;
        $pageId = $catalog->page_id;
        $locationId = $catalog->dealer_location_id;

        // Set Default Catalog Type
        if(empty($catalogType)) {
            $catalogType = Catalog::VEHICLE_TYPE;
        }

        // Return JSON Encoded Test Payload
        return '{"' . $catalogId . '":{"catalog_id":"' . $catalogId . '","catalog_type":"' . $catalogType . '","business_id":"' . $businessId . '","dealer_id":"1001","location_id":"' . $locationId . '","account_id":"' . $accountId . '","page_id":"' . $pageId . '","name":"Marcel Test Dealership","phone":"8665826900","address":"{\"addr1\":\"200 Test Dealership Parkway\",\"city\":\"Elkhart\",\"region\":\"IN\",\"postal\":\"46770\",\"country\":\"US\"}","website":"www.testdealershipindiana.com","listings":[{"fb_page_id":"' . $pageId . '","vehicle_id":"1923231","title":"2020 ABU Trail-Lite YJATJALALYT Travel Trailer RV","description":"2020 ABU Trail-Lite YJATJALALYT Travel Trailer RV is Available<br><br>Square cornered side unload door with drop down feed door in front of first horse with nylon strap (not available on 2H) <br>Hitch Type: Gooseneck <br>Load Type: Slant <br>Trailer Width: 6\' 7\" <br>> Trailer Length: 2015 & newer model year: 2H = 15\'8\", 17\'9\", 19\'9\" or 21\'9\"; 3H = 19\'2\", 21\'3\", 23\'3\" or 25\'3\"; 4H = 22\'8\", 24\'9\", 26\'9\" or 28\'9\"; 5H = 26\'2\" or 27\'11\"; 2014 model year: 2H = 15\' 4\", 17\' 5\", 19\' 5\" or 21\' 5\"; 3H = 18\' 10\", 20\' 11\", 22\' 11\" or 24\' 11\"; 4H = 22\' 4\", 24\' 5\", 26\' 5\" or 28\' 5\"; 5H = 25\' 10\" or 27\' 11\"<br><br><br>Horse Stalls: 2, 3, 4 or 5-horse <br>Stall Width: 42\" with 48\" in last stall <br>Dressing Room Size: 2015 & newer model year: 2H-4H = 42\", 67\", 91\" or 115\" short wall; 5H = 42\" or 67\" short wall; 2014 model year: 2H-4H = 52\", 77\", 101\" or 125\" short wall; 5H = 52\" or 77\" short wall <br>Inside Height : 7\' 0\" <br>Exterior Sides: White .040 aluminum exterior sheets with bottom \"Wave\" side panels; Trailer includes colored front gooseneck sheets (white is standard) <br>Rear Doors: Double rear doors with windows, pipe hardware and no center post; Nylon strap at rear door <br>Escape Doors: Square cornered side unload door with drop down feed door in front of first horse with nylon strap (not available on 2H) <br>Windows: Drop down feed door with drop down bars in front of each horse; 36\" x 22\" window with bars behind each horse; Two 24\" x 20\" windows in gooseneck <br>Tack Area: Folding rear tack wall lined on one side; Mid tack optional <br>Living Quarters: Available with living quarters <br>Stall Dividers: Slant stall dividers with shoulder separator and spring-loaded slam latch <br>Weight: For the weight of this particular model, please download and reference Trailer Weight Index for gooseneck horse trailers. <br>Warranty: 10-year limited structural warranty; 1-year bumper-to-bumper warranty. Transferable warranty available on qualified trailers. See warranty page or dealer for more info. <br><br>One of Featherlite\'s \'lite-priced\' gooseneck horse trailers, the Model 8533 is available in a 2, 3, 4 or 5-horse gooseneck. This slant load trailer is 6\'7\" wide with 42\" stalls and an inside height of 7\'0\". This gooseneck trailer includes a dressing room and double rear doors with pipe hardware. <br>Additional Features & Benefits<br>- Saddle Rack: Removable post with solid mounted saddle racks (2H, 3H, 4H = one per horse; 5H = 4); One swinging blanket bar; Extra location for removable saddle rack post in dressing room<br>- Rubber on Walls: Rubber up 48\" on wall behind horses & up 44\" on wall in front of horses<br>- Floor Mats: 3\/4\" rubber mats in horse area<br>- Roof Vents: One pop-up roof vent per horse<br>- Dome Lights: Dome light in horse area, rear tack and dressing room <br>- Dressing <br>- Room: 36\" deluxe camper door (square cornered, no screen); turf on floor, drop wall and gooseneck floor; brush tray<br>- - o 3500# rubber torsion axles with electric brakes; 3H = Two 5200# rubber torsion axles with electric brakes; 4H & 5H = Two 6000# rubber torsion axles with electric brakes; E-Z Lube axles standard <br>- Tire\/Rim: 2H = 16\" 6-hole silver [modular wheels with](http:\/\/test\/) [ST235\/80R16](http:\/\/test\/) Load Range D tires (Qty: 4); 3H, 4H & 5H = 16\" 8-hole silver modular wheels with ST235\/80R16 Load Range E tires (Qty: 4); Goodyear tires standard <br>- Exterior Lights: LED clearance lights (Qty: 14) and 4 wraparound LED stop\/turn lights <br>- Tapered Gooseneck: Standard <br>- Coupler: 2 5\/16\" adjustable gooseneck coupler; single speed landing gear<br>More: Spare tire carrier; 6 hook removable halter bar; 6 hook solid halter bar; rubber dock bumper <br><br>testing, testing, 1, 2, 3","url":"","make":"ABU","brand":"Trail-Lite","model":"YJATJALALYT","year":"2020","mileage_value":"","mileage_unit":"MI","drivetrain":null,"transmission":null,"image":[{"url":"http:\/\/lv-inv.dealer-cdn.com\/eQNHLd\/7pJvAO\/2020_ABU_Trail-Lite_YJATJALALYT_Travel_Trailer_RV_Y4eRou.jpg"},{"url":"http:\/\/lv-inv.dealer-cdn.com\/eQNHLd\/7pJvAO\/2020_ABU_Trail-Lite_YJATJALALYT_Travel_Trailer_RV_AONFiK.jpg"},{"url":"http:\/\/lv-inv.dealer-cdn.com\/eQNHLd\/7pJvAO\/2020_ABU_Trail-Lite_YJATJALALYT_Travel_Trailer_RV_mi63ai.jpg"}],"body_style":"Travel Trailer","vin":"TUATIAK","price":"5652.00","exterior_color":null,"state_of_vehicle":"new","fuel_type":"","condition":"EXCELLENT","sale_price":"0.00","availability":"1","vehicle_type":"camping_rv","stock_number":"114455","dealer_id":"' . $locationId . '","dealer_name":"Marcel Test Dealership","dealer_phone":"8665826900","dealer_communication_channel":"CHAT","dealer_privacy_policy_url":"www.testdealershipindiana.com","address_addr1":"200 Test Dealership Parkway","address_city":"Elkhart","address_region":"IN","address_country":"US","address_postal":"46770"},{"fb_page_id":"' . $pageId . '","vehicle_id":"1925876","title":"2020 ABU 14000 lb. Dovetail Skidsteer Trailer Equipment Trailer","description":"2020 ABU 14000 lb. Dovetail Skidsteer Trailer Equipment Trailer is Available<br><br>testing, tesitng, t1 2 3 <br><br><br><br>The skid steer series is a valuable asset to those who haul equipment for construction, commercial lawn care, landscaping, small excavation and much more. Skid steer trailers are built with a 6\" channel hitch, frame and cross members along with stake pockets down each side to secure your cargo. Trailer sizes range from 80\" and 82\" wide and from 16\' to 20\' long. Additional options are also available and can be added for a custom build. <br><br>G.V.W.R range from 14,000 to 21,000 lbs. <br><br>14,000 lb. Dovetail Skidsteer Trailer <br><br>Standard Features <br><br>- 16\'-20\' Lengths Straight ( Longer Lengths Available) <br>- 6\" Channel Frame <br>- (2) 7,000 lb. Easy Lube Axles <br>- Electric Brakes <br>- 2-5\/16 Adjustable Coupler <br>- 12,000 lb. Drop Leg Jack <br>- 60\" Fold Up Ramps <br>- Wood Floor <br>- Stake Pockets <br>- Removable Teardrop Fenders <br>- LED Rubber Mounted Lights <br>- Tire: ST235\/80R16- 10 ply <br>- Wheels: 16x6JJ 8 Bolt Wheels Optional Features <br>- 2\' Wood Beavertail <br>- Gooseneck Coupler <br>- Pintle Coupler <br>- 3rd Axle <br>- Surge Brakes <br>- Torsion <br>- Drop Stands <br>- Steel Floor <br>- Treated Wood Floor <br>- Paint Underside <br>- Tread Plate Fenders <br>- Stored Ramps <br>- Spare Tire Bracket <br>- Spare Tire & Wheel","url":"","make":"ABU","brand":null,"model":"14000 lb. Dovetail Skidsteer Trailer","year":"2020","mileage_value":null,"mileage_unit":"MI","drivetrain":null,"transmission":null,"image":[{"url":"http:\/\/lv-inv.dealer-cdn.com\/eQNHLd\/pDjl4q\/2020_ABU_14000_lb._Dovetail_Skidsteer_Trailer_Equipment_Trailer_CLIutE.jpg"}],"body_style":"Equipment Trailer","vin":"5571785108","price":"5000.25","exterior_color":"bronze","state_of_vehicle":"new","fuel_type":null,"condition":"EXCELLENT","sale_price":"0.00","availability":"1","vehicle_type":"equipment","stock_number":"GN102X","dealer_id":"' . $locationId . '","dealer_name":"Marcel Test Dealership","dealer_phone":"8665826900","dealer_communication_channel":"CHAT","dealer_privacy_policy_url":"www.testdealershipindiana.com","address_addr1":"200 Test Dealership Parkway","address_city":"Elkhart","address_region":"IN","address_country":"US","address_postal":"46770"},{"fb_page_id":"' . $pageId . '","vehicle_id":"1928733","title":"2018 102 Ironworks Testing Seconday Flat Bed","description":"2018 102 Ironworks Testing Seconday Flat Bed is Available<br><br>Note: Our inventory changes daily because we have such an amazing website and highly efficient marketing system that generates tons of leads by automatically posting our inventory to Facebook Marketplace, Craigslist and other classified sites that we can barely keep anything on the Lot for more than 24 hours... so please, call first!!","url":"","make":"102 Ironworks","brand":null,"model":"Testing Seconday","year":"2018","mileage_value":null,"mileage_unit":"MI","drivetrain":null,"transmission":null,"image":[{"url":"http:\/\/lv-inv.dealer-cdn.com\/eQNHLd\/Y3M90y\/2018_102_Ironworks_Testing_Seconday_Flat_Bed_AjOkIS.png"},{"url":"http:\/\/lv-inv.dealer-cdn.com\/eQNHLd\/Y3M90y\/2018_102_Ironworks_Testing_Seconday_Flat_Bed_n37YAq.png"}],"body_style":"Flat Bed","vin":"444654646654654","price":"4545.00","exterior_color":null,"state_of_vehicle":"new","fuel_type":null,"condition":"EXCELLENT","sale_price":"0.00","availability":"1","vehicle_type":"semi_flatbed","stock_number":"Secondary testing","dealer_id":"' . $locationId . '","dealer_name":"Marcel Test Dealership","dealer_phone":"8665826900","dealer_communication_channel":"CHAT","dealer_privacy_policy_url":"www.testdealershipindiana.com","address_addr1":"200 Test Dealership Parkway","address_city":"Elkhart","address_region":"IN","address_country":"US","address_postal":"46770"},{"fb_page_id":"' . $pageId . '","vehicle_id":"2103198","title":"2020 Advantage test_model2020 Equipment Trailer","description":"2020 Advantage test_model2020 Equipment Trailer is Available<br><br>Note: Our inventory changes daily because we have such an amazing website and highly efficient marketing system that generates tons of leads by automatically posting our inventory to Facebook Marketplace, Craigslist and other classified sites that we can barely keep anything on the Lot for more than 24 hours... so please, call first!!","url":"","make":"Advantage","brand":null,"model":"test_model2020","year":"2020","mileage_value":null,"mileage_unit":"MI","drivetrain":null,"transmission":null,"image":[{"url":"http:\/\/lv-inv.dealer-cdn.com\/eQNHLd\/qdyw3k\/2020_Advantage_test_model2020_Equipment_Trailer_2qPj4S.png"}],"body_style":"Equipment Trailer","vin":"1212","price":"100.00","exterior_color":"","state_of_vehicle":"new","fuel_type":null,"condition":"EXCELLENT","sale_price":"0.00","availability":"1","vehicle_type":"equipment","stock_number":"testing_stag_pic","dealer_id":"' . $locationId . '","dealer_name":"Marcel Test Dealership","dealer_phone":"8665826900","dealer_communication_channel":"CHAT","dealer_privacy_policy_url":"www.testdealershipindiana.com","address_addr1":"200 Test Dealership Parkway","address_city":"Elkhart","address_region":"IN","address_country":"US","address_postal":"46770"}]}}';
    }
}
