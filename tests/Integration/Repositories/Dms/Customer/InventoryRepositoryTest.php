<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Dms\Customer;

use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\Inventory\Inventory;
use App\Repositories\Dms\Customer\InventoryRepository;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PDOException;
use Tests\database\seeds\Dms\Customer\InventorySeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class InventoryRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var InventorySeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @typeOfTest IntegrationTestCase
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(InventoryRepository::class, $concreteRepository);
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
     * @group DMS_INVENTORY
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers InventoryRepository::getAll
     */
    public function testGetAllWithBasisOperations(array $params, int $expectedTotal): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call getAll without pagination and valid parameters
        // Then I got a list of inventories without pagination
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
     * @param  string|null  $expectedTitleName
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers InventoryRepository::getAll
     */
    public function testGetAllWithPagination(
        array $params,
        int $expectedTotal,
        int $expectedLastPage,
        ?string $expectedTitleName
    ): void {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call getAll with pagination and valid parameters
        /** @var LengthAwarePaginator $inventories */
        $inventories = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params), true);

        /** @var Inventory $firstRecord */
        $firstRecord = $inventories->first();

        // Then I should get a class which is an instance of LengthAwarePaginator
        self::assertInstanceOf(LengthAwarePaginator::class, $inventories);
        // And I should see that total of inventories is the expected,
        self::assertSame($expectedTotal, $inventories->total());
        // And I should see the last page is the expected
        self::assertSame($expectedLastPage, $inventories->lastPage());
        // And I should see the first inventory is the expected
        self::assertSame($firstRecord ? $firstRecord->title: $firstRecord, $expectedTitleName);
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers InventoryRepository::create
     *
     * @group DMS
     * @group DMS_INVENTORY
     */
    public function testCreate(): void {
        $this->seeder->seed();
        // Given I have a collection of inventories not related yet to the customer
        $inventories = $this->seeder->unrelatedInventories;

        // And I have a customer id
        $customerId = $this->seeder->customer->getKey();

        // When I call create with valid parameters
        /** @var CustomerInventory $inventoryRelatedToCustomer */
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
     * Test that SUT is throwing a PDOException when some constraint is not being satisfied
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider invalidPropertiesProvider
     *
     * @param  array  $properties
     * @param  string|callable  $expectedPDOExceptionMessage
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers InventoryRepository::create
     * @group DMS
     * @group DMS_INVENTORY
     */
    public function testCreateWithException(
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
     * Test that SUT is deleting correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers InventoryRepository::bulkDestroy
     * @group DMS
     * @group DMS_INVENTORY
     */
    public function testBulkDestroy(): void {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // And I have a customer id
        $customerId = $this->seeder->customer->getKey();

        // When I call bulkDestroy with a valid list of valid ids
        $deletion = $this->getConcreteRepository()->bulkDestroy(array_slice($this->seeder->customerInventoryIds, 1));

        // Then I should get a boolean true value
        self::assertTrue($deletion);
        // And I should see that total of inventories related to the customer is one
        self::assertSame(1, CustomerInventory::where(['customer_id' => $customerId])->count());
    }

    /**
     * Examples of parameters with expected total, last page numbers, and the first inventory title name.
     *
     * @return array[]
     */
    public function validQueryParametersProvider(): array
    {
        $dealerIdLambda = static function (InventorySeeder $seeder) {
            return $seeder->dealer->getKey();
        };

        $customerIdLambda = static function (InventorySeeder $seeder) {
            return $seeder->customer->getKey();
        };

        return [                                                                               // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedName
            'By dummy dealer paged'                                                            => [['dealer_id' => $dealerIdLambda, 'per_page' => 3], 8, 3, 'SOLD 2013 SOONER 3 HORSE WITH DRESS RM1'],
            'By dummy dealer filtered with matches'                                            => [['dealer_id' => $dealerIdLambda, 'search_term' => 'Snowmobile Trailer'], 2, 1, '2020 Adirondack j6i66i Snowmobile Trailer'],
            'By dummy dealer filtered without matches'                                         => [['dealer_id' => $dealerIdLambda, 'search_term' => 'Batmobile'], 0, 1, null],
            'By dummy customer paged'                                                          => [['dealer_id' => $dealerIdLambda, 'customer_id' => $customerIdLambda, 'per_page' => 2], 3, 2, '102 Ironworks Dump Truck'],
            'By dummy customer, no tenancy condition and `andWhere` condition sorted by title' => [['dealer_id' => $dealerIdLambda, 'customer_id' => $customerIdLambda, 'customer_condition' => '-has', 'sort' => '-title', 'andWhere' => [['vin', '<>', '12345678901234567']]], 4, 1, '2017 Adventure Sports Products Adventure Testing Horse Trailer'],
            'By dummy customer, no tenancy condition sorted by title desc'                     => [['dealer_id' => $dealerIdLambda, 'customer_id' => $customerIdLambda, 'customer_condition' => '-has', 'sort' => 'title'], 5, 1, 'Windsurf board Magic Wave PRO']
        ];
    }

    /**
     * Examples of invalid customer-inventory id properties with theirs expected exception messages.
     *
     * @return array[]
     */
    public function invalidPropertiesProvider(): array
    {
        $customerIdLambda = static function (InventorySeeder $seeder) {
            return $seeder->customer->getKey();
        };

        $customerRelatedInventoryIdLambda = static function (InventorySeeder $seeder): int {
            $inventories = $seeder->customerRelatedInventories;
            return $inventories[array_rand($inventories, 1)]->getKey();
        };

        $nonExistentCustomerLambda = function (...$params) {
            return $this->getCannotInsertOrUpdateMessage(
                CustomerInventory::getTableName(),
                'dms_customer_inventory_customer_id_foreign'
            );
        };

        $nonExistentInventoryLambda = function (...$params) {
            return $this->getCannotInsertOrUpdateMessage(
                CustomerInventory::getTableName(),
                'dms_customer_inventory_inventory_id_foreign'
            );
        };

        $duplicateEntryLambda = function (int $customerId, int $inventoryId) {
            return $this->getDuplicateEntryMessage(
                "$customerId-$inventoryId",
                'dms_customer_inventory_customer_id_inventory_id_unique'
            );
        };

        return [                             // array $properties, string $expectedPDOExceptionMessage
            'With non-existent customer'  => [['customer_id' => 666999, 'inventory_id' => $customerRelatedInventoryIdLambda], $nonExistentCustomerLambda],
            'With non-existent inventory' => [['customer_id' => $customerIdLambda, 'inventory_id' => 666999], $nonExistentInventoryLambda],
            'With duplicate entry'        => [['customer_id' => $customerIdLambda, 'inventory_id' => $customerRelatedInventoryIdLambda], $duplicateEntryLambda],
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
