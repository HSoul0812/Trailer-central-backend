<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Inventory;

use App\Models\Inventory\Category;
use App\Repositories\Inventory\CategoryRepository;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 */
class CategoryRepositoryTest extends TestCase
{
    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_INVENTORY_CATEGORY
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @note IntegrationTestCase
     */
    public function testIoCForCategoryRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(CategoryRepository::class, $concreteRepository);
    }

    /**
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @note IntegrationTestCase
     */
    public function testGetAllFilterWithoutPaging(): void
    {
        $fakeCategory = factory(Category::class)->create([
            'entity_type_id' => 2,
        ]);

        /** @var EloquentCollection $categories */
        $categories = $this->getConcreteRepository()->getAll([
            'search_term' => $fakeCategory->label,
            'entity_type_id' => 2,
        ]);

        $this->assertInstanceOf(EloquentCollection::class, $categories);
        $this->assertTrue($categories->contains('label', $fakeCategory->label));

        $this->removeFakeCategories(collect([$fakeCategory]));
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY_CATEGORY
     *
     * @note IntegrationTestCase
     */
    public function testGetAllFilterWithPaging(): void
    {
        $categoryRepository = $this->getConcreteRepository();
        $this->assertInstanceOf(CategoryRepository::class, $categoryRepository);

        $fakeCategories = $this->getFakeCategories();

        $this->getTestParams()->each(function (array $param) use ($categoryRepository) {
            /** @var LengthAwarePaginator $categories */
            $categories = $categoryRepository->getAll($param[0], true);

            $this->assertInstanceOf(LengthAwarePaginator::class, $categories);
            $this->assertTrue($categories->contains('label', $param[1]));
            $this->assertFalse($categories->contains('label', $param[2]));
        });

        $this->removeFakeCategories($fakeCategories);
    }

    /**
     * @return CategoryRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     */
    protected function getConcreteRepository(): CategoryRepositoryInterface
    {
        return $this->app->make(CategoryRepositoryInterface::class);
    }

    /**
     * Get the fake vendors
     *
     * @return Collection
     */
    private function getFakeCategories(): Collection
    {
        $fakeCategories = collect([]);

        $fakeCategories->push(factory(Category::class)->create([
            'entity_type_id' => 1,
            'label' => 'New Category Unit Test',
        ]));

        $fakeCategories->push(factory(Category::class)->create([
            'entity_type_id' => 2,
            'label' => 'New Horse Trailer Category Unit Test',
        ]));

        return $fakeCategories;
    }

    /**
     * @param Collection $categories
     *
     * @return void
     */
    private function removeFakeCategories(Collection $categories): void
    {
        $categories->each(function (Category $category) {
            $category->forceDelete();
        });
    }

    /**
     * Get test params
     *
     * @return Collection
     */
    private function getTestParams(): Collection
    {
        return collect([
            [
                ['entity_type_id' => 1, 'search_term' => 'New Category Unit Test'],
                'New Category Unit Test',
                'New Horse Trailer Category Unit Test',
            ],
            [
                ['entity_type_id' => 2, 'search_term' => 'New Horse Trailer Category Unit Test'],
                'New Horse Trailer Category Unit Test',
                'New Category Unit Test',
            ],
        ]);
    }
}
