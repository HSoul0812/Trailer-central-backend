<?php


namespace Tests\Integration\Services\Dms\Customer;

use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Services\Dms\Customer\CustomerService;
use App\Services\Dms\Customer\CustomerServiceInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\database\seeds\Dms\Customer\CustomerSeeder;
use Tests\database\seeds\Dms\Customer\InventorySeeder;
use Tests\database\seeds\User\DealerLocationSeeder;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * @var CustomerService $service
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(CustomerServiceInterface::class);
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testNonExistingImport() {
        $dealers = new DealerLocationSeeder();
        $dealers->seed();
        $dealerId = $dealers->dealers[0]->getKey();
        $dealerLocationId = $dealers->locations[$dealerId][0]->getKey();

        $this->runImportTest($dealerId, $dealerLocationId,'John', 'Doe', Str::random(16));
        $dealers->cleanUp();
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testExistingCustomerImport() {
        $seeder = new CustomerSeeder();
        $seeder->seed();
        $dealer = $seeder->dealers[0];
        $dealerLocation = $seeder->dealerLocations[0];
        $customer = $seeder->customers[0][0];

        $this->runImportTest(
            $dealer->getKey(),
            $dealerLocation->getKey(),
            $customer->first_name,
            $customer->last_name,
            Str::random(16)
        );
        $seeder->cleanUp();
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testExistingInventoryImport() {
        $seeder = new InventorySeeder();
        $seeder->seed();
        $dealer = $seeder->dealer;
        $dealerLocation = $seeder->dealerLocation;
        $customer = $seeder->customer;
        $inventory = $seeder->unrelatedInventories[0];
        $this->runImportTest($dealer->getKey(), $dealerLocation->getKey(), $customer->first_name, $customer->last_name, $inventory->vin);
        $seeder->cleanUp();
    }

    /**
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @return void
     */
    public function testExistingCustomerInventoryImport() {
        $seeder = new InventorySeeder();
        $seeder->seed();
        $dealer = $seeder->dealer;
        $dealerLocation = $seeder->dealerLocation;
        $customer = $seeder->customer;
        $inventory = $seeder->customerRelatedInventories[0];
        $this->runImportTest($dealer->getKey(), $dealerLocation->getKey(), $customer->first_name, $customer->last_name, $inventory->vin);
        $seeder->cleanUp();
    }

    private function runImportTest(
        int $dealerId,
        int $dealerLocationId,
        string $testFirstName,
        string $testLastName,
        string $testVin
    ) {
        $csvData = [
            $testLastName,
            $testFirstName,
            '10001',
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
            $testVin,
            '',
            1,
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
            $dealerId,
            $dealerLocationId,
            $popular_type,
            $category
        );

        $this->assertDatabaseHas('dms_customer', ['first_name' => $testFirstName, 'last_name' => $testLastName]);
        $this->assertDatabaseHas('inventory', ['vin' => $testVin]);

        $activeCustomer = Customer::where([
            ['display_name', '=',  "$testFirstName $testLastName"],
            ['dealer_id', '=', $dealerId]
        ])->first();

        $inventory = Inventory::where('vin', $testVin)->first();
        $this->assertDatabaseHas('dms_customer_inventory', [
            'customer_id' => $activeCustomer->getKey(),
            'inventory_id' => $inventory->getKey()
        ]);
    }
}
