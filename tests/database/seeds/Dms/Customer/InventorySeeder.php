<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms\Customer;

use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Traits\WithGetter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\database\seeds\Seeder;

/**
 * @property-read Customer $customer
 * @property-read User $dealer
 * @property-read DealerLocation $dealerLocation
 * @property-read array<Inventory> $customerRelatedInventories
 * @property-read array<Inventory> $unrelatedInventories
 * @property-read string[] $customerInventoryIds
 */
class InventorySeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var DealerLocation
     */
    private $dealerLocation;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Inventory[]
     */
    private $customerRelatedInventories = [];

    /**
     * @var Inventory[]
     */
    private $unrelatedInventories = [];

    /**
     * @var string[]
     */
    private $customerInventoryIds = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->customer = factory(Customer::class)->create();
        $this->dealer = $this->customer->dealer;
        $this->dealerLocation = factory(DealerLocation::class)->create(['dealer_id' => $this->dealer->getKey()]);
    }

    public function seed(): void
    {
        $customerId = $this->customer->getKey();

        $seeds = [
            ['title' => 'SOLD 2013 SOONER 3 HORSE WITH DRESS RM1'],
            ['title' => '2020 Adirondack j6i66i Snowmobile Trailer', 'vin' => '12345678901234567'],
            ['title' => '2021 Adirondack G6i77j Snowmobile Trailer'],
            ['title' => 'Windsurf board Magic Wave PRO'],
            ['title' => '2017 Adventure Sports Products Adventure Testing Horse Trailer'],
            ['title' => '102 Ironworks Dump Truck', 'customer_id' => $customerId],
            ['title' => '103 Ironworks Dump Truck', 'customer_id' => $customerId],
            ['title' => 'Wayland Dump Truck', 'customer_id' => $customerId]
        ];

        collect($seeds)->each(function (array $seed) use ($customerId): void {
            $inventory = factory(Inventory::class)->create([
                'dealer_id' => $this->dealer->getKey(),
                'title' => $seed['title'],
                'vin' => $seed['vin'] ?? Str::random(17)
            ]);

            $inventoryId = $inventory->getKey();

            if (isset($seed['customer_id'])) {
                $customerInventory = CustomerInventory::create(['customer_id' => $customerId, 'inventory_id' => $inventoryId]);
                $this->customerRelatedInventories[] = $inventory;
                $this->customerInventoryIds[] = $customerInventory->getKey();

                return;
            }

            $this->unrelatedInventories[] = $inventory;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        // Database clean up
        DB::table('parts_orders_status')->where('dealer_id', '=', $dealerId)->delete();
        Inventory::where('dealer_id', $dealerId)->delete();
        Customer::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
        DealerLocation::where('dealer_id', $dealerId)->delete();
    }
}
