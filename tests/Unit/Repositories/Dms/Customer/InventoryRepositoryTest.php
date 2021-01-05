<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Dms\Customer;

use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\Inventory\Inventory;
use App\Repositories\Dms\Customer\InventoryRepository;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use App\Traits\WithGetter;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PDOException;
use Tests\database\seeds\Dms\Customer\InventorySeeder;
use Tests\TestCase;
use Tests\Unit\WithMySqlDataBaseConstraintViolationsParser;


class InventoryRepositoryTest extends TestCase
{
    use WithGetter;
    use WithMySqlDataBaseConstraintViolationsParser;

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
        $categories = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

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
        $inventories = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params), true);

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
     * @covers InventoryRepository::create
     * @note IntegrationTestCase
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     */
    public function testCreateIsWorkingAsExpectedWhenTheUniqueIndexIsBeingSatisfied(): void {
        $this->seeder->seed();
        // Given I have a collection of inventories not related yet to the customer
        $inventories = $this->seeder->unrelatedInventories;

        $customerId = $this->seeder->customer->getKey();

        /** @var CustomerInventory $inventoryRelatedToCustomer */
        // When I call create with valid parameters
        $inventoryRelatedToCustomer = $this->getConcreteRepository()->create([
            'customer_id' => $customerId,
            'inventory_id' => $inventories[array_rand($inventories, 1)]->getKey(),
        ]);

        // Then I should get a class which is an instance of CustomerInventory
        self::assertInstanceOf(CustomerInventory::class, $inventoryRelatedToCustomer);
        // And I should see that total of inventories related to the customer has incremented in one record
        self::assertSame(4, CustomerInventory::where(['customer_id' => $customerId])->count());
    }

    /**
     * @dataProvider creationInvalidPropertiesProvider
     *
     * @param  array  $properties
     * @param  string|callable  $expectedPDOExceptionMessage
     * @covers       InventoryRepository::create
     * @note IntegrationTestCase
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     */
    public function testCreateIsThrowingAPDOExceptionWhenTheConstrainsIsNotBeingSatisfied(
        array $properties,
        $expectedPDOExceptionMessage
    ): void {
        // Given I have a collection of inventories
        $this->seeder->seed();

        $properties = $this->seeder->extractValues($properties);
        $expectedPDOExceptionMessage = is_callable($expectedPDOExceptionMessage) ?
            $expectedPDOExceptionMessage($properties['customer_id'], $properties['inventory_id']) :
            $expectedPDOExceptionMessage;

        // When I call create with invalid parameters
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage($expectedPDOExceptionMessage);


        /** @var null $inventoryRelatedToCustomer */
        $inventoryRelatedToCustomer = $this->getConcreteRepository()->create([
            'customer_id' => $properties['customer_id'],
            'inventory_id' => $properties['inventory_id'],
        ]);

        // And I should get a null value
        self::assertNull($inventoryRelatedToCustomer);
    }

    /**
     * @covers InventoryRepository::bulkDestroy
     * @note IntegrationTestCase
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     */
    public function testBulkDestroyIsWorkingAsExpected(): void {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call create with valid parameters
        $deletion = $this->getConcreteRepository()->bulkDestroy(array_slice($this->seeder->customerInventoryIds, 1));

        // Then I should get boolean true
        self::assertTrue($deletion);
        // And I should see that total of inventories related to the customer is zero
        self::assertSame(1, CustomerInventory::where(['customer_id' => $this->seeder->customer->getKey()])->count());
    }

    /**
     * Examples of parameters with expected total, last page numbers, and first inventory title name.
     *
     * @return array[]
     */
    public function queryWellRequestedParametersAndSummariesProvider(): array
    {
        $dealerIdLambda = static function (InventorySeeder $seeder) {
            return $seeder->dealer->getKey();
        };

        $customerIdLambda = static function (InventorySeeder $seeder) {
            return $seeder->customer->getKey();
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

    /**
     * Examples of invalid properties with expected exception message.
     *
     * @return array[]
     */
    public function creationInvalidPropertiesProvider(): array
    {
        $customerIdLambda = static function (InventorySeeder $seeder) {
            return $seeder->customer->getKey();
        };

        $customerRelatedInventoryIdLambda = static function (InventorySeeder $seeder): int {
            $inventories = $seeder->customerRelatedInventories;
            return $inventories[array_rand($inventories, 1)]->getKey();
        };

        return [                     // array $properties, string $expectedPDOExceptionMessage
            'Non-existent customer'  => [['customer_id' => 666999, 'inventory_id' => $customerRelatedInventoryIdLambda], function(int $customerId, int $inventoryId){ return $this->getMessageForCannotInsertOrUpdateConstraint(CustomerInventory::getTableName(), 'dms_customer_inventory_customer_id_foreign');}],
            'Non-existent inventory' => [['customer_id' => $customerIdLambda, 'inventory_id' => 666999], function(int $customerId, int $inventoryId){ return $this->getMessageForCannotInsertOrUpdateConstraint(CustomerInventory::getTableName(), 'dms_customer_inventory_inventory_id_foreign');}],
            'Duplicate entry'        => [['customer_id' => $customerIdLambda, 'inventory_id' => $customerRelatedInventoryIdLambda], function(int $customerId, int $inventoryId){ return $this->getMessageForDuplicateEntryConstraint("$customerId-$inventoryId", 'dms_customer_inventory_customer_id_inventory_id_unique');}],
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
