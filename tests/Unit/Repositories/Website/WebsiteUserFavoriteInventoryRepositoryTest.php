<?php
namespace Tests\Unit\Repositories\Website;

use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepository;
use Mockery;

class WebsiteUserFavoriteInventoryRepositoryTest extends \Tests\TestCase
{
    private $websiteUserFavoriteInventoryMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->websiteUserFavoriteInventoryMock = $this->getEloquentMock(WebsiteUserFavoriteInventory::class);
        $this->app->instance(WebsiteUserFavoriteInventory::class, $this->websiteUserFavoriteInventoryMock);
    }
    public function testCreate() {
        $testParams = ['website_user_id' => 1, 'inventory_id' => 1];

        $this->websiteUserFavoriteInventoryMock->website_user_id = $testParams['website_user_id'];
        $this->websiteUserFavoriteInventoryMock->inventory_id = $testParams['inventory_id'];

        $this->websiteUserFavoriteInventoryMock
            ->shouldReceive('firstOrCreate')
            ->andReturnSelf();
        $repository = $this->getConcreteRepository();
        $favoriteInventory = $repository->create($testParams);
        $this->assertEquals($favoriteInventory->website_user_id, $testParams['website_user_id']);
        $this->assertEquals($favoriteInventory->inventory_id, $testParams['inventory_id']);
    }

    public function testDeleteBulk() {
        $testParams = [
            'website_user_id' => 1,
            'inventory_ids' => [1, 2, 3, 4, 5]
        ];

        $query = Mockery::mock(\StdClass::class);

        $this->websiteUserFavoriteInventoryMock
            ->shouldReceive('where')
            ->andReturn($query);

        $query->shouldReceive('whereIn')
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('delete')
            ->once()
            ->andReturnSelf();

        $repository = $this->getConcreteRepository();
        $repository->deleteBulk($testParams);
    }

    public function testGetAll() {
        $testParams = [
            'website_user_id' => 1
        ];

        $query = Mockery::mock(\StdClass::class);
        $this->websiteUserFavoriteInventoryMock
            ->shouldReceive('where')
            ->andReturn($query);
        $query->shouldReceive('get')
            ->andReturn([]);

        $repository = $this->getConcreteRepository();
        $result = $repository->getAll($testParams);
        $this->assertIsArray($result);
    }

    public function getConcreteRepository():WebsiteUserFavoriteInventoryRepository {
        return app()->make(WebsiteUserFavoriteInventoryRepository::class);
    }
}
