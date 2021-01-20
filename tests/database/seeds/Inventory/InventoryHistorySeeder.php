<?php

declare(strict_types=1);

namespace Tests\database\seeds\Inventory;

use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryHistory;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;
use Illuminate\Database\Query\Builder;

/**
 * @property-read Inventory $inventory
 * @property-read array<Customer> $customers
 * @property-read array<InventoryHistory> $transactions
 * @property-read User $dealer
 * @property-read Faker $faker
 */
class InventoryHistorySeeder extends Seeder
{
    use WithGetter;

    public const INVENTORY_ID = 1000000000;

    public const CUSTOMER_ID = 2000000000;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var array<Customer>
     */
    private $customers = [];

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var User
     */
    private $faker;

    /**
     * @var InventoryHistory
     */
    private $transactions = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        // It is necessary to clean up before feed because PHPUnit is not able to tearDown when occurs a failure
        Inventory::destroy(self::INVENTORY_ID);
        Customer::destroy(self::CUSTOMER_ID);

        $this->faker = Faker::create();

        $this->dealer = factory(User::class)->create();
        $this->inventory = factory(Inventory::class)->create([
            'inventory_id' => self::INVENTORY_ID, 'dealer_id' => $this->dealer->getKey()
        ]);
    }

    public function seed(): void
    {
        $dealerId = $this->dealer->getKey();

        $customerSeeds = [
            ['first_name' => 'Walter', 'last_name' => 'White'],
            ['first_name' => 'Jesse', 'last_name' => 'Pinkman'],
            ['first_name' => 'Saul', 'last_name' => 'Goodman'],
        ];

        // We dont want that Mike be randomly picked up
        factory(Customer::class)->create(['first_name' => 'Mike', 'last_name' => 'Ehrmantraut', 'id' => self::CUSTOMER_ID]);

        foreach ($customerSeeds as $seed) {
            //Lets create an array of customer indexed by customer name
            $customerName = $seed['first_name'] . ' ' . $seed['last_name'];

            $this->customers[$customerName] = factory(Customer::class)->create(array_merge($seed, ['dealer_id' => $dealerId]));
        }

        $seeds = [
            ['dealer_id' => $dealerId, 'customer_id' => $this->customers['Walter White']->id, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_ESTIMATE],
            ['dealer_id' => $dealerId, 'customer_id' => $this->getRandomCustomer()->id, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_ESTIMATE],
            ['dealer_id' => $dealerId, 'customer_id' => $this->getRandomCustomer()->id, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_ESTIMATE],
            ['dealer_id' => $dealerId, 'customer_id' => self::CUSTOMER_ID, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_RETAIL],
            ['dealer_id' => $dealerId, 'customer_id' => $this->customers['Jesse Pinkman']->id, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_ESTIMATE],
            ['dealer_id' => $dealerId, 'customer_id' => $this->customers['Saul Goodman']->id, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_WARRANTY],
            ['dealer_id' => $dealerId, 'customer_id' => $this->getRandomCustomer()->id, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_ESTIMATE],
            ['dealer_id' => $dealerId, 'customer_id' => $this->getRandomCustomer()->id, 'inventory_id' => self::INVENTORY_ID, 'type' => ServiceOrder::TYPE_INTERNAL]
        ];

        foreach ($seeds as $seed) {
            factory(ServiceOrder::class)->create($seed);
        }

        $this->transactions = InventoryHistory::where(['inventory_id' => self::INVENTORY_ID])->get()->toArray();
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        ServiceOrder\ServiceItem::whereIn('repair_order_id', static function (Builder $query) use ($dealerId) {
            $query->select('id')
                ->from(with(new ServiceOrder())->getTable())
                ->where('dealer_id', $dealerId);
        })->delete();
        ServiceOrder::where('dealer_id', $dealerId)->delete();
        Inventory::where('dealer_id', $dealerId)->delete();
        Customer::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }

    public function getRandomCustomer(): ?Customer
    {
        return !empty($this->customers) ? $this->faker->randomElement($this->customers) : null;
    }
}
