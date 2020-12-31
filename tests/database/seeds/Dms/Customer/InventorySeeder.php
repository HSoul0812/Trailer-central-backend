<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms\Customer;

use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Traits\WithGetter;
use Illuminate\Support\Str;
use Tests\database\seeds\SeederInterface;

/**
 * @property-read Customer $customer
 * @property-read User $dealer
 */
class InventorySeeder implements SeederInterface
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->customer = factory(Customer::class)->create();
        $this->dealer = $this->customer->dealer;
    }

    public function seed():void
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

        collect($seeds)->map(function (array $seed) use ($customerId): Inventory {
            $inventory = factory(Inventory::class)->create([
                'dealer_id' => $this->dealer->getKey(),
                'title' => $seed['title'],
                'vin' => $seed['vin'] ?? Str::random(18)
            ]);

            if (isset($seed['customer_id'])) {
                CustomerInventory::create(['customer_id' => $customerId, 'inventory_id' => $inventory->getKey()]);
            }

            return $inventory;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        // Database clean up
        CustomerInventory::where('customer_id', $this->customer->getKey())->delete();
        Inventory::where('dealer_id', $dealerId)->delete();
        Customer::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }
}
