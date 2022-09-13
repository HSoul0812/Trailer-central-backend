<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Inventory;

use App\Models\Inventory\Category;
use App\Repositories\Inventory\CategoryRepository;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_INVENTORY_CATEGORY
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForCategoryRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(CategoryRepository::class, $concreteRepository);
    }

    /**
     * @dataProvider queryParametersAndSummariesProvider
     *
     * @param array $params  list of query parameters
     * @param int $expectedTotal
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testGetAllIsNotPaginatingAndItIsFilteringAsExpected(
        array $params,
        int $expectedTotal
    ): void {
        /** @var Collection $categories */
        $categories = $this->getConcreteRepository()->getAll($params);

        self::assertInstanceOf(Collection::class, $categories);
        self::assertSame($expectedTotal, $categories->count());
    }

    /**
     * @dataProvider queryParametersAndSummariesProvider
     *
     * @group DMS
     * @group DMS_INVENTORY_CATEGORY
     *
     * @param array $params  list of query parameters
     * @param int $expectedTotal
     * @param int $expectedLastPage
     * @param string $expectedFirstCategoryLabel
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testGetAllIsPaginatingFilteringAndSortingAsExpected(
        array $params,
        int $expectedTotal,
        int $expectedLastPage,
        string $expectedFirstCategoryLabel
    ): void {
        /** @var LengthAwarePaginator $categories */
        $categories = $this->getConcreteRepository()->getAll($params, true);

        /** @var Category $firstRecord */
        $firstRecord = $categories->first();

        self::assertInstanceOf(LengthAwarePaginator::class, $categories);
        self::assertSame($expectedTotal, $categories->total());
        self::assertSame($expectedLastPage, $categories->lastPage());
        self::assertSame($firstRecord->label, $expectedFirstCategoryLabel);
    }

    /**
     * Examples of parameters, expected total and last page numbers, and first category label.
     *
     * @return array[]
     */
    public function queryParametersAndSummariesProvider(): array
    {
        return [//             array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedFirstCategoryLabel
            'No parameters' => [[], 124, 9, 'ATV Trailer'],
            'Trailer or Truck Bed sorted by title asc' => [['entity_type_id' => 1, 'sort' => '-title'], 23, 2, 'ATV Trailer'],
            'Horse Trailer sorted by label desc' => [['entity_type_id' => 2 , 'sort' => 'label'], 1, 1, 'Horse Trailer'],
        ];
    }

    /**
     * @return CategoryRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): CategoryRepositoryInterface
    {
        return $this->app->make(CategoryRepositoryInterface::class);
    }
}
