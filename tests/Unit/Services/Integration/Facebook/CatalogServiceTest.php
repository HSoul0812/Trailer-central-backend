<?php

namespace Tests\Unit\Services\Integration\Facebook;

use App\Jobs\Files\DeleteS3FilesJob;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Repository;
use App\Services\Inventory\BusinessService;
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
     * @var LegacyMockInterface|TokenRepositoryInterface
     */
    private $tokenRepositoryMock;

    /**
     * @var int
     */
    private $testTokenId = 0;

    public function setUp(): void
    {
        parent::setUp();

        $this->testTokenId = $_ENV['TEST_TOKEN_ID'];

        $this->tokenRepositoryMock = Mockery::mock(TokenRepositoryInterface::class);
        $this->app->instance(TokenRepositoryInterface::class, $this->tokenRepositoryMock);
    }

    /**
     * @covers ::validate
     * @dataProvider deleteParamsProvider
     *
     * @param $imageParams
     * @param $fileParams
     * @throws BindingResolutionException
     */
    public function testValidate($imageParams, $fileParams)
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

        /** @var BusinessService $service */
        $service = $this->app->make(BusinessService::class);

        $result = $service->delete($inventoryId);

        $this->assertTrue($result);
    }
}
