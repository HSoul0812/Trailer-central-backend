<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Models\Integration\Facebook\Catalog;
use App\Jobs\Integration\Facebook\CatalogJob;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Repository;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Facebook\CatalogService;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var LegacyMockInterface|CatalogRepositoryInterface
     */
    private $catalogRepositoryMock;

    /**
     * @var LegacyMockInterface|PageRepositoryInterface
     */
    private $pageRepositoryMock;

    /**
     * @var LegacyMockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var LegacyMockInterface|AuthServiceInterface
     */
    private $authServiceMock;

    /**
     * @var LegacyMockInterface|BusinessServiceInterface
     */
    private $businessServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->businessServiceMock = Mockery::mock(BusinessServiceInterface::class);
        $this->app->instance(BusinessServiceInterface::class, $this->businessServiceMock);

        //$this->authServiceMock = Mockery::mock(AuthServiceInterface::class);
        //$this->app->instance(AuthServiceInterface::class, $this->authServiceMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);

        $this->pageRepositoryMock = Mockery::mock(PageRepositoryInterface::class);
        $this->app->instance(PageRepositoryInterface::class, $this->pageRepositoryMock);

        $this->catalogRepositoryMock = Mockery::mock(CatalogRepositoryInterface::class);
        $this->app->instance(CatalogRepositoryInterface::class, $this->catalogRepositoryMock);
    }

    /**
     * @covers ::show
     *
     * @throws BindingResolutionException
     */
    public function testShow()
    {
        // Get Test Catalog
        $catalogId = (int) $_ENV['TEST_FB_CATALOG_ID'];
        $catalog = Catalog::find($catalogId);
        $validate = ['is_valid' => true, 'is_expired' => false];

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $catalogId])
            ->andReturn($catalog);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($catalog->accessToken)
            ->andReturn($validate);

        // Validate Show Catalog Result
        $result = $service->show(['id' => $catalogId]);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalogId);
    }

    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Get Test Catalog
        $catalogId = (int) $_ENV['TEST_FB_CATALOG_ID'];
        $catalog = Catalog::find($catalogId);
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
            ->andReturn($catalog->page);

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
                'access_token' => $catalog->accessToken->refresh_token
            ]);

        // Mock Create Catalog Access Token
        $this->tokenRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createAuthParams)
            ->andReturn($catalog->accessToken);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($catalog->accessToken)
            ->andReturn($validate);

        // Validate Create Catalog Result
        $result = $service->create($createRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalogId);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @covers ::update
     *
     * @throws BindingResolutionException
     */
    public function testUpdate()
    {
        // Get Test Catalog
        $catalogId = (int) $_ENV['TEST_FB_CATALOG_ID'];
        $catalog = Catalog::find($catalogId);
        $validate = ['is_valid' => true, 'is_expired' => false];

        // Update Request Params
        $updateRequestParams = [
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

        // Update Catalog Params
        $updateCatalogParams = $updateRequestParams;
        $updateCatalogParams['fbapp_page_id'] = $catalog->page->id;

        // Relation Auth Params
        $relationAuthParams = $updateCatalogParams;
        $relationAuthParams['token_type'] = 'facebook';
        $relationAuthParams['relation_type'] = 'fbapp_catalog';
        $relationAuthParams['relation_id'] = $catalog->id;

        // Update Auth Params
        $updateAuthParams = $relationAuthParams;
        $updateAuthParams['refresh_token'] = $catalog->accessToken->refresh_token;

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Update Page
        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($updateRequestParams)
            ->andReturn($catalog->page);

        // Mock Update Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($updateCatalogParams)
            ->andReturn($catalog);

        // Mock Get Relation Token
        $this->tokenRepositoryMock
            ->shouldReceive('getRelation')
            ->once()
            ->with($relationAuthParams)
            ->andReturn([
                'access_token' => $catalog->accessToken->refresh_token
            ]);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($catalog->accessToken)
            ->andReturn($validate);

        // Validate Update Catalog Result
        $result = $service->update($updateRequestParams);

        // Assert Match
        $this->assertSame($result['data']['id'], $catalogId);

        // Assert Match
        $this->assertSame($result['validate'], $validate);
    }

    /**
     * @covers ::delete
     *
     * @throws BindingResolutionException
     */
    public function testDelete()
    {
        // Get Test Catalog
        $catalogId = (int) $_ENV['TEST_FB_CATALOG_ID'];
        $catalog = Catalog::find($catalogId);

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $catalogId])
            ->andReturn($catalog);

        // Mock Delete Feed
        $this->businessServiceMock
            ->shouldReceive('deleteFeed')
            ->once()
            ->with($catalog->accessToken, $catalog->catalog_id, $catalog->feed_id);

        // Mock Delete Access Token
        $this->authServiceMock
            ->shouldReceive('delete')
            ->once()
            ->with([
                'token_type' => 'facebook',
                'relation_type' => 'fbapp_catalog',
                'relation_id' => $catalogId
            ]);

        // Mock Delete Catalog
        $this->catalogServiceMock
            ->shouldReceive('delete')
            ->once()
            ->with($catalogId)
            ->andReturn(true);

        // Validate Delete Catalog Result
        $result = $service->delete($catalogId);

        // Assert Match
        $this->assertTrue($result);
    }

    /**
     * @covers ::validate
     * @dataProvider deleteParamsProvider
     *
     * @param $imageParams
     * @param $fileParams
     * @throws BindingResolutionException
     */
    /*public function testValidate($imageParams, $fileParams)
    {
        $inventoryId = PHP_INT_MAX;
        $imageModels = new Collection();
        $fileModels = new Collection();

        $this->imageRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $imageParams)
            ->andReturn($imageModels);

        $inventoryDeleteParams = [
            'id' => $inventoryId,
            'imageIds' => [$imageModel2->image_id],
            'fileIds' => [$fileModel1->id],
        ];

        $this->expectsJobs(DeleteS3FilesJob::class);

        $this->fileRepositoryMock
            ->shouldReceive('getAllByInventoryId')
            ->once()
            ->with($inventoryId, $fileParams)
            ->andReturn($fileModels);

        $this->inventoryRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($inventoryDeleteParams)
            ->andReturn(true);

        $this->expectsJobs(DeleteS3FilesJob::class);

        Log::shouldReceive('info')
            ->with('Item has been successfully deleted', ['inventoryId' => $inventoryId]);

        // @var CatalogService $service
        $service = $this->app->make(CatalogService::class);

        $result = $service->delete($inventoryId);

        $this->assertTrue($result);
    }*/
}
