<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Models\Integration\Facebook\Catalog;
use App\Jobs\Integration\Facebook\CatalogJob;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Repository;
use App\Services\Integration\Facebook\AuthServiceInterface;
use App\Services\Integration\Facebook\CatalogService;
use App\Services\Integration\Facebook\CatalogServiceInterface;
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
     * @var LegacyMockInterface|CatalogServiceInterface
     */
    private $businessServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->catalogRepositoryMock = Mockery::mock(CatalogRepositoryInterface::class);
        $this->app->instance(CatalogRepositoryInterface::class, $this->catalogRepositoryMock);

        $this->pageRepositoryMock = Mockery::mock(PageRepositoryInterface::class);
        $this->app->instance(PageRepositoryInterface::class, $this->pageRepositoryMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);

        $this->authServiceMock = Mockery::mock(AuthServiceInterface::class);
        $this->app->instance(AuthServiceInterface::class, $this->authServiceMock);

        $this->businessServiceMock = Mockery::mock(CatalogServiceInterface::class);
        $this->app->instance(CatalogServiceInterface::class, $this->businessServiceMock);        
    }

    /**
     * @covers ::show
     *
     * @throws BindingResolutionException
     */
    public function testShow()
    {
        // Get Test Catalog
        $catalogId = $_ENV['TEST_FB_CATALOG_ID'];
        $catalog = Catalog::find($catalogId);
        $validate = ['is_valid' => true, 'is_expired' => true];

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Get Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with($catalogId)
            ->andReturn($catalog);

        // Mock Validate Access Token
        $this->businessServiceMock
            ->shouldReceive('validate')
            ->once()
            ->with($catalog->accessToken)
            ->andReturn($validate);

        // Validate Show Catalog Result
        $result = $service->show($catalogId);

        // Assert Match
        $this->assertSame($result, [
            'data' => $catalog,
            'validate' => $validate
        ]);
    }

    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate()
    {
        // Get Test Catalog
        $catalogId = $_ENV['TEST_FB_CATALOG_ID'];
        $catalog = Catalog::find($catalogId);
        $validate = ['is_valid' => true, 'is_expired' => true];

        // Create Page Params
        $createPageParams = [
            'page_title' => $catalog->page->title,
            'page_id' => $catalog->page->page_id,
            'is_active' => 1
        ];

        // Create Catalog Params
        $createCatalogParams = [
            'dealer_id' => $catalog->dealer_id,
            'dealer_location_id' => $catalog->dealer_location_id,
            'fbapp_page_id' => $catalog->page->id,
            'business_id' => $catalog->business_id,
            'catalog_id' => $catalog->catalog_id,
            'account_name' => $catalog->account_name,
            'account_id' => $catalog->account_id,
            'feed_id' => $catalog->feed_id,
            'filters' => '',
            'is_active' => 1
        ];

        // Create Auth Params
        $refreshAuthParams = [
            'token_type' => 'facebook',
            'relation_type' => 'fbapp_catalog',
            'relation_id' => $catalog->id,
            'access_token' => $catalog->accessToken->access_token
        ];

        // Create Auth Params
        $createAuthParams = $refreshAuthParams;
        $createAuthParams['refresh_token'] = $catalog->accessToken->refresh_token;
        $createAuthParams['expires_in'] = $catalog->accessToken->expires_in;
        $createAuthParams['expires_at'] = $catalog->accessToken->expires_at;
        $createAuthParams['issued_at'] = $catalog->accessToken->issued_at;

        /** @var CatalogService $service */
        $service = $this->app->make(CatalogService::class);

        // Mock Create Page
        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createPageParams)
            ->andReturn($catalog->page);

        // Mock Create Catalog
        $this->catalogRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($createCatalogParams)
            ->andReturn($catalog);

        // Mock Create Catalog Access Token
        $this->authServiceMock
            ->shouldReceive('refresh')
            ->once()
            ->with($refreshAuthParams)
            ->andReturn([
                'refresh_token' => $catalog->accessToken->refresh_token,
                'expires_in' => $catalog->accessToken->expires_in,
                'expires_at' => $catalog->accessToken->expires_at
            ]);

        // Mock Create Catalog Access Token
        $this->authServiceMock
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

        // Validate Show Catalog Result
        $result = $service->show($catalogId);

        // Assert Match
        $this->assertSame($result, [
            'data' => $catalog,
            'validate' => $validate
        ]);
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
