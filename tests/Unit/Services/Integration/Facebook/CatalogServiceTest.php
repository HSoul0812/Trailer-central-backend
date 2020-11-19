<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Jobs\Integration\Facebook\CatalogJob;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Repository;
use App\Services\Integration\Facebook\AuthServiceInterface;
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
 * Test for App\Services\Integration\Facebook\BusinessService
 *
 * Class BusinessServiceTest
 * @package Tests\Unit\Services\Integration\Facebook
 *
 * @coversDefaultClass \App\Services\Inventory\BusinessService
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

        $this->catalogRepositoryMock = Mockery::mock(CatalogRepositoryInterface::class);
        $this->app->instance(CatalogRepositoryInterface::class, $this->catalogRepositoryMock);

        $this->pageRepositoryMock = Mockery::mock(PageRepositoryInterface::class);
        $this->app->instance(PageRepositoryInterface::class, $this->pageRepositoryMock);

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);

        $this->authServiceMock = Mockery::mock(AuthServiceInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->authServiceMock);

        $this->businessServiceMock = Mockery::mock(BusinessServiceInterface::class);
        $this->app->instance(BusinessServiceInterface::class, $this->businessServiceMock);        
    }

    /**
     * @covers ::show
     *
     * @throws BindingResolutionException
     */
    public function testShow()
    {
        // Get Test Catalog ID
        $catalogId = $_ENV['TEST_FB_CATALOG_ID'];

        /** @var BusinessService $service */
        $service = $this->app->make(CatalogService::class);

        // Validate Show Catalog Result
        $result = $service->show($catalogId);

        // Assert is Valid
        $this->assertTrue($result['validate']['is_valid']);

        // Assert Is Not Expired
        $this->assertFalse($result['validate']['is_expired']);
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

        // @var BusinessService $service
        $service = $this->app->make(BusinessService::class);

        $result = $service->delete($inventoryId);

        $this->assertTrue($result);
    }*/
}
