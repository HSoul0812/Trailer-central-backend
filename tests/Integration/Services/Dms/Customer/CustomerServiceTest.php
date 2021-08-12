<?php


namespace Tests\Integration\Services\Dms\Customer;

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

    public function testNonExistingImport() {
        $dealers = new DealerLocationSeeder();
        $dealers->seed();
        $dealer_id = $dealers->dealers[0]->getKey();
        $dealer_location_id = $dealers->locations[$dealer_id][0]->getKey();

        $this->runImportTest($dealer_id, $dealer_location_id,'John', 'Doe', Str::random(16));
        $dealers->cleanUp();
    }

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
        $active_nur = null;
        $active_customer = null;
        $popular_type = 1;
        $category = 'atv';

        $this->service->importCSV(
            $csvData,
            2,
            $active_nur,
            $active_customer,
            $dealer_id,
            $dealer_location_id,
            $popular_type,
            $category
        );

        $this->assertEquals($test_nur, $active_nur);

        $this->assertNotNull($active_customer);
        $this->assertEquals($active_customer->first_name, $test_first_name);
        $this->assertEquals($active_customer->last_name, $test_last_name);

        $this->assertDatabaseHas('dms_customer', ['first_name' => $test_first_name, 'last_name' => $test_last_name]);
        $this->assertDatabaseHas('inventory', ['vin' => $test_vin]);

        $inventory = Inventory::where('vin', $test_vin)->first();
        $this->assertDatabaseHas('dms_customer_inventory', [
            'customer_id' => $active_customer->getKey(),
            'inventory_id' => $inventory->getKey()
        ]);
    }
}
