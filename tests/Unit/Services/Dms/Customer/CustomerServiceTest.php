<?php


namespace Tests\Unit\Services\Dms\Customer;

use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Dms\Customer\InventoryRepository as CustomerInventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Dms\Customer\CustomerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    /**
     * @var CustomerService $service
     */
    private $service;

    /**
     * @var CustomerRepository|Mockery\LegacyMockInterface|Mockery\MockInterface $customerRepository
     */
    private $customerRepository;

    /**
     * @var InventoryRepository|Mockery\LegacyMockInterface|Mockery\MockInterface $inventoryRepository
     */
    private $inventoryRepository;

    /**
     * @var CustomerInventoryRepository|Mockery\LegacyMockInterface|Mockery\MockInterface $customerInventoryRepository
     */
    private $customerInventoryRepository;

    /**
     * @var int $dealer_id
     */
    private $dealer_id;

    /**
     * @var int $dealer_location_id
     */
    private $dealer_location_id;

    /**
     * @var string $unit_serial
     */
    private $unit_serial;

    /**
     * @var Customer $customer
     */
    private $customer;

    /**
     * @var Inventory $inventory
     */
    private $inventory;

    /**
     * @var CustomerInventory $customerInventory
     */
    private $customerInventory;

    public function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = Mockery::mock(CustomerRepository::class);
        $this->inventoryRepository = Mockery::mock(InventoryRepository::class);
        $this->customerInventoryRepository = Mockery::mock(CustomerInventoryRepository::class);

        // Fixtures
        $this->dealer_id = 1001;
        $this->dealer_location_id = 1;
        $this->unit_serial = Str::random(16);

        $this->customer = new Customer();
        $this->customer->id = 1;

        $this->inventory = new Inventory();
        $this->inventory->inventory_id = 2;

        $this->customerInventory = new CustomerInventory([
            'customer_id' => 1,
            'inventory_id' => 2
        ]);

        $this->service = new CustomerService(
            $this->customerRepository,
            $this->inventoryRepository,
            $this->customerInventoryRepository
        );
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testNonExistingImport() {
        $this->customerRepository
            ->shouldReceive('get')
            ->once()
            ->andThrow(new ModelNotFoundException());
        $this->inventoryRepository
            ->shouldReceive('get')
            ->once()
            ->andThrow(new ModelNotFoundException());
        $this->customerRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset(['first_name' => 'John', 'last_name' => 'Doe']))
            ->andReturn($this->customer);

        $this->inventoryRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset(['vin' => $this->unit_serial]))
            ->andReturn($this->inventory);

        $this->customerInventoryRepository->shouldReceive('get')
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->customerInventoryRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset(['inventory_id' => 2, 'customer_id' => 1]))
            ->andReturn($this->customerInventory);

        $this->runImportTest($this->dealer_id, $this->dealer_location_id,'John', 'Doe', $this->unit_serial);
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testExistingCustomerImport() {
        $this->customerRepository
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->customer);
        $this->inventoryRepository
            ->shouldReceive('get')
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->inventoryRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset(['vin' => $this->unit_serial]))
            ->andReturn($this->inventory);

        $this->customerInventoryRepository->shouldReceive('get')
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->customerInventoryRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset([
                'inventory_id' => $this->inventory->getKey(),
                'customer_id' => $this->customer->getKey()
            ]))
            ->andReturn($this->customerInventory);

        $this->runImportTest($this->dealer_id, $this->dealer_location_id,'John', 'Doe', $this->unit_serial);
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testExistingInventoryImport() {
        $this->customerRepository
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->customer);
        $this->inventoryRepository
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->inventory);


        $this->customerInventoryRepository->shouldReceive('get')
            ->once()
            ->andThrow(new ModelNotFoundException());

        $this->customerInventoryRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset([
                'inventory_id' => $this->inventory->getKey(),
                'customer_id' => $this->customer->getKey()
            ]))
            ->andReturn($this->customerInventory);

        $this->runImportTest($this->dealer_id, $this->dealer_location_id,'John', 'Doe', $this->unit_serial);
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testExistingCustomerInventoryImport() {
        $this->customerRepository
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->customer);
        $this->inventoryRepository
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->inventory);

        $this->customerInventoryRepository->shouldReceive('get')
            ->once()
            ->andReturn($this->customerInventory);

        $this->runImportTest($this->dealer_id, $this->dealer_location_id,'John', 'Doe', $this->unit_serial);
    }

    private function runImportTest(
        int $dealer_id,
        int $dealer_location_id,
        string $test_first_name,
        string $test_last_name,
        string $test_vin
    ) {
        $test_nur = '10001';
        $test_entity_type_id = 1;
        $csvData = [
            $test_last_name,
            $test_first_name,
            $test_nur,
            'Cra 432 Port #3',
            '',
            'Stockholm',
            'IN',
            '13524',
            '(123) 123-123',
            '(345) 345-345',
            '',
            '',
            '2000',
            'Henchel',
            'KwK40',
            $test_vin,
            '',
            $test_entity_type_id,
            '',
            '',
            'GRAY',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $popular_type = 1;
        $category = 'atv';

        $this->service->importCSV(
            $csvData,
            2,
            $dealer_id,
            $dealer_location_id,
            $popular_type,
            $category
        );
    }
}
