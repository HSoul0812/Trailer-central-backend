<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Dms\Customer;

use App\Models\Inventory\Inventory;
use App\Repositories\Dms\Customer\InventoryRepository;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class InventoryRepositoryTest extends TestCase
{
    /**
     * Test that SUT is properly bound by the application
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForTheRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(InventoryRepository::class, $concreteRepository);
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
     * @param  array  $params  list of query parameters
     * @param  int  $expectedTotal
     * @param  int  $expectedLastPage
     * @param  string|null  $expectedTitleName
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testGetAllIsPaginatingAndFilteringAsExpected(
        array $params,
        int $expectedTotal,
        int $expectedLastPage,
        ?string $expectedTitleName
    ): void {
        /** @var LengthAwarePaginator $inventories */
        $inventories = $this->getConcreteRepository()->getAll($params, true);

        /** @var Inventory $firstRecord */
        $firstRecord = $inventories->first();

        self::assertInstanceOf(LengthAwarePaginator::class, $inventories);
        self::assertSame($expectedTotal, $inventories->total());
        self::assertSame($expectedLastPage, $inventories->lastPage());
        self::assertSame($firstRecord ? $firstRecord->title: $firstRecord, $expectedTitleName);
    }

    /**
     * Examples of parameters with expected total, last page numbers, and first inventory title name.
     *
     * @return array[]
     */
    public function queryParametersAndSummariesProvider(): array
    {
        return [                                                                                  // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedName
            'Dummy dealer'                                                                     => [['dealer_id' => 1001], 939, 63, 'SOLD    2013 SOONER 3 HORSE WITH DRESS RM1'],
            'Dummy dealer and filtered with matches'                                           => [['dealer_id' => 1001, 'search_term' => 'Snowmobile Trailer'], 5, 1, '2020 Adirondack j6i66i Snowmobile Trailer'],
            'Dummy dealer and filtered with no matches'                                        => [['dealer_id' => 1001, 'search_term' => 'Batmobile'], 0, 1, null],
            'Customer from Dummy dealer'                                                       => [['dealer_id' => 1001, 'customer_id' => 7149], 17, 2, '2017 Adventure Sports Products Adventure Testing Horse Trailer'],
            'Customer from Dummy dealer with does not have condition and sorted by title asc'  => [['dealer_id' => 1001, 'customer_id' => 7149, 'customer_condition' => '-has', 'sort' => '-title'], 922, 62, '102 Ironworks  Dump Truck'],
            'Customer from Dummy dealer with does not have condition and sorted by title desc' => [['dealer_id' => 1001, 'customer_id' => 7149, 'customer_condition' => '-has', 'sort' => 'title'], 922, 62, 'xxxx']
        ];
    }

    /**
     * @return InventoryRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): InventoryRepositoryInterface
    {
        return $this->app->make(InventoryRepositoryInterface::class);
    }
}
