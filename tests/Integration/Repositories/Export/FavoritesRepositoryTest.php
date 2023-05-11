<?php

namespace Tests\Integration\Repositories\Export;

use App\Models\Inventory\Category;
use App\Models\Inventory\Inventory;
use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Models\Website\Website;
use App\Repositories\Export\FavoritesRepository;
use App\Repositories\Export\FavoritesRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 */
class FavoritesRepositoryTest extends TestCase
{
    /**
     * @var Website
     */
    private $website;

    /**
     * @var WebsiteUser
     */
    private $user;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Collection<Inventory>
     */
    private $inventories;

    const INVENTORY_COUNT = 5;

    /**
     * Test that SUT is properly bound by the application
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForFavoritesRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(FavoritesRepository::class, $concreteRepository);
    }

    /**
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testGetThrowsAnInvalidArgumentExceptionWhenNoWebsiteIdIsPresent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("A website id is required");
        $this->getConcreteRepository()->get([]);
    }

    /**
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testGetRetrievesAllWebsiteUsersWithTheirFavoriteInventories(): void
    {
        $this->createTestData();

        $data = $this->getConcreteRepository()->get(['website_id' => $this->website->id]);
        self::assertCount(1, $data);
        $websiteUser = $data->first();
        self::assertCount(self::INVENTORY_COUNT, $websiteUser->favoriteInventories);

        $returnedFavoritesIds = $websiteUser->favoriteInventories->map(function ($favorite) {
            return $favorite->inventory_id;
        })->sort()->toArray();

        $createdInventoryIds = $this->inventories->map(function ($inventory) {
            return $inventory->inventory_id;
        })->sort()->toArray();

        self::assertSame($returnedFavoritesIds, $createdInventoryIds);

        $this->cleanUpTestData();
    }

    /**
     * @return FavoritesRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): FavoritesRepositoryInterface
    {
        return $this->app->make(FavoritesRepositoryInterface::class);
    }

    private function createTestData()
    {
        $this->website = factory(Website::class)->create();
        $this->user = factory(WebsiteUser::class)->create(['website_id' => $this->website->id]);
        $this->category = factory(Category::class)->create();
        $this->inventories = factory(Inventory::class, self::INVENTORY_COUNT)->create(['dealer_id' => $this->website->dealer_id, 'category' => $this->category->inventory_category_id]);
        WebsiteUserFavoriteInventory::insert($this->inventories->map(function ($inventory) {
            return ['website_user_id' => $this->user->id, 'inventory_id' => $inventory->inventory_id];
        })->toArray());
    }

    private function cleanUpTestData()
    {
        $this->website->delete();
        $this->user->delete();
        $this->inventories->each(function ($inventory) {
            $inventory->delete();
        });
        $this->category->delete();
    }
}
