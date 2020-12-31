<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Dms\Customer;

use App\Models\Inventory\Inventory;
use App\Repositories\Dms\Customer\InventoryRepository;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use App\Traits\WithGetter;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\database\seeds\Dms\Customer\InventorySeeder;
use Tests\TestCase;

class InventoryRepositoryTest extends TestCase
{
    use WithGetter;

    /**
     * @var InventorySeeder
     */
    private $seeder;

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
     * @dataProvider queryWellRequestedParametersAndSummariesProvider
     *
     * @param array $params  list of query parameters
     * @param int $expectedTotal
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     * @covers InventoryRepository::getAll
     */
    public function testGetAllIsNotPaginatingAndItIsFilteringAsExpected(array $params, int $expectedTotal): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        /** @var Collection $categories */
        // When I call getAll without pagination with valid parameters
        $categories = $this->getConcreteRepository()->getAll($this->extractValues($params));

        // Then I should get a class which is an instance of Collection
        self::assertInstanceOf(Collection::class, $categories);
        self::assertSame($expectedTotal, $categories->count());
    }

    /**
     * @dataProvider queryWellRequestedParametersAndSummariesProvider
     *
     * @param  array  $params  list of query parameters
     * @param  int  $expectedTotal
     * @param  int  $expectedLastPage
     * @param  string|null  $expectedTitleName
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     * @covers InventoryRepository::getAll
     */
    public function testGetAllIsPaginatingAndFilteringAsExpected(
        array $params,
        int $expectedTotal,
        int $expectedLastPage,
        ?string $expectedTitleName
    ): void {
        // Given I have a collection of inventories
        $this->seeder->seed();

        /** @var LengthAwarePaginator $inventories */
        // When I call getAll with pagination with valid parameters
        $inventories = $this->getConcreteRepository()->getAll($this->extractValues($params), true);

        /** @var Inventory $firstRecord */
        $firstRecord = $inventories->first();

        // Then I should get a class which is an instance of LengthAwarePaginator
        self::assertInstanceOf(LengthAwarePaginator::class, $inventories);
        // And I should see that total of inventories, the last page and the title of the first inventory
        // is the same as I expected
        self::assertSame($expectedTotal, $inventories->total());
        self::assertSame($expectedLastPage, $inventories->lastPage());
        self::assertSame($firstRecord ? $firstRecord->title: $firstRecord, $expectedTitleName);
    }

    /**
     * Examples of parameters with expected total, last page numbers, and first inventory title name.
     *
     * @return array[]
     */
    public function queryWellRequestedParametersAndSummariesProvider(): array
    {
        $dealerIdLambda = static function (self $that) {
            return $that->seeder->dealer->getKey();
        };

        $customerIdLambda = static function (self $that) {
            return $that->seeder->customer->getKey();
        };

        return [                                                                                                            // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedName
            'Dummy dealer paged'                                                                                            => [['dealer_id' => $dealerIdLambda, 'per_page' => 3], 8, 3, 'SOLD 2013 SOONER 3 HORSE WITH DRESS RM1'],
            'Dummy dealer and filtered with matches'                                                                        => [['dealer_id' => $dealerIdLambda, 'search_term' => 'Snowmobile Trailer'], 2, 1, '2020 Adirondack j6i66i Snowmobile Trailer'],
            'Dummy dealer and filtered with no matches'                                                                     => [['dealer_id' => $dealerIdLambda, 'search_term' => 'Batmobile'], 0, 1, null],
            'Dummy customer is paged'                                                                                       => [['dealer_id' => $dealerIdLambda, 'customer_id' => $customerIdLambda, 'per_page' => 2], 3, 2, '102 Ironworks Dump Truck'],
            'Dummy customer which does not have condition also is sorted by title asc and is filtered with extra condition' => [['dealer_id' => $dealerIdLambda, 'customer_id' => $customerIdLambda, 'customer_condition' => '-has', 'sort' => '-title', 'andWhere' => [['vin', '<>', '12345678901234567']]], 4, 1, '2017 Adventure Sports Products Adventure Testing Horse Trailer'],
            'Dummy customer which does not have condition also is sorted by title desc'                                     => [['dealer_id' => $dealerIdLambda, 'customer_id' => $customerIdLambda, 'customer_condition' => '-has', 'sort' => 'title'], 5, 1, 'Windsurf board Magic Wave PRO']
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new InventorySeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
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
