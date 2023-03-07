<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Inventory;

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\Inventory\InventoryHistory;
use App\Repositories\Inventory\InventoryHistoryRepository;
use App\Repositories\Inventory\InventoryHistoryRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\database\seeds\Inventory\InventoryHistorySeeder;
use Tests\TestCase;

class InventoryHistoryRepositoryTest extends TestCase
{
    /**
     * @var InventoryHistorySeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_INVENTORY_HISTORY
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(InventoryHistoryRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProvider
     *
     * @param array $params  list of query parameters
     * @param int $expectedTotal
     *
     * @group DMS
     * @group DMS_INVENTORY_HISTORY
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers InventoryHistoryRepository::getAll
     */
   public function testGetAllWithBasisOperations(array $params, int $expectedTotal): void
    {
        // Given I have a collection of inventory transactions
        $this->seeder->seed();

        // When I call getAll without pagination and valid parameters
        // Then I got a list of inventory transactions without pagination
        /** @var Collection $inventories */
        $inventories = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $inventories);
        // And the total of records should be the expected
        self::assertSame($expectedTotal, $inventories->count());
    }

    /**
     * Test that SUT is performing all operations sort, filter and pagination
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProvider
     *
     * @param  array  $params  list of query parameters
     * @param  int  $expectedTotal
     * @param  int  $expectedLastPage
     * @param  string|null  $expectedCustomerName
     *
     * @group DMS
     * @group DMS_INVENTORY_HISTORY
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers InventoryHistoryRepository::getAll
     */
    public function testGetAllWithPagination(
        array $params,
        int $expectedTotal,
        int $expectedLastPage,
        ?string $expectedCustomerName
    ): void {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // Given I have a collection of inventory transactions
        /** @var LengthAwarePaginator $transactions */
        $transactions = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params), true);

        /** @var InventoryHistory $firstRecord */
        $firstRecord = $transactions->first();

        // Then I should get a class which is an instance of LengthAwarePaginator
        self::assertInstanceOf(LengthAwarePaginator::class, $transactions);
        // And I should see that total of inventory transactions is the expected
        self::assertSame($expectedTotal, $transactions->total());
        // And I should see the last page is the expected
        self::assertSame($expectedLastPage, $transactions->lastPage());
        // And I should see the first inventory transaction is the expected
        self::assertSame($firstRecord ? $firstRecord->customer_name: $firstRecord, $expectedCustomerName);
    }

    /**
     * Examples of parameters with expected total, last page numbers, and the first inventory transaction customer name.
     *
     * @return array[]
     */
    public function validQueryParametersProvider(): array
    {
        $inventoryIdLambda = static function (InventoryHistorySeeder $seeder) {
            return $seeder->inventory->getKey();
        };

        $customerIdLambda = static function (InventoryHistorySeeder $seeder) {
            return $seeder->fixedUser->getKey();
        };

        return [                                             // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedCustomerName
            'By inventory paged'                             => [['inventory_id' => $inventoryIdLambda,'per_page' => 3], 8, 3, 'Walter White'],
            'By inventory sorted by customer and paged'      => [['inventory_id' => $inventoryIdLambda,'per_page' => 3, 'sort' => '-customer_name'], 8, 3, 'Jesse Pinkman'],
            'By inventory sorted by customer desc and paged' => [['inventory_id' => $inventoryIdLambda,'per_page' => 3, 'sort' => 'customer_name'], 8, 3, 'Walter White'],
            'By inventory and customer'                      => [['inventory_id' => $inventoryIdLambda,'customer_id' => $customerIdLambda], 1, 1, 'Mike Ehrmantraut'],
            'By inventory filtered with matches'             => [['inventory_id' => $inventoryIdLambda, 'search_term' => 'Ehrmantraut'], 1, 1, 'Mike Ehrmantraut'],
            'By inventory filtered without matches'          => [['inventory_id' => $inventoryIdLambda, 'search_term' => 'Zaul Goodman'], 0, 1, null],
            'By inventory filtered by `andWhere` condition'  => [['inventory_id' => $inventoryIdLambda, 'andWhere' => [['subtype', '=', ServiceOrder::TYPE_RETAIL]]], 1, 1, 'Mike Ehrmantraut'],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new InventoryHistorySeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return InventoryHistoryRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     */
    protected function getConcreteRepository(): InventoryHistoryRepositoryInterface
    {
        return $this->app->make(InventoryHistoryRepositoryInterface::class);
    }
}
