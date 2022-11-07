<?php

declare(strict_types=1);

namespace Tests\database\seeds\Part;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrder;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPart;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Models\Parts\CacheStoreTime;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property-read User $dealer
 */
class PartSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var array
     */
    private $configs;

    /**
     * PartSeeder constructor.
     *
     * @param int $configs define how many items should be created, if its not passed, a random amount of items will be generated.
     */
    public function __construct(array $configs = [])
    {
        $this->configs = $configs;
        $this->dealer = factory(User::class)->create();
    }

    public function seed(): void
    {
        $dealerId = $this->dealer->getKey();
        $faker = Faker::create();

        $parts = factory(
            Part::class,
            ($this->configs['count'] ?? 0) === 0 ? random_int(10, 20) : $this->configs['count']
        )->create([
            'dealer_id' => $dealerId,
            'dealer_cost' => $faker->randomFloat(2, 0, 50),
            'latest_cost' => $faker->randomFloat(2, 0, 50)
        ]);

        if (count($this->configs['with'] ?? []) > 0) {
            Model::unguard();
            $parts->each(function (Part $part) use ($faker) {
                foreach ($this->configs['with'] as $with) {
                    switch($with) {
                        case 'purchaseOrders': {
                            $purchaseOrder = PurchaseOrder::create([
                                'dealer_id' => $part->dealer_id,
                                'vendor_id' => $part->vendor_id,
                                'status' => 'ordered',
                                'user_defined_id' => Str::random(5)
                            ]);
                            $part->purchaseOrders()->save(new PurchaseOrderPart([
                                'purchase_order_id' => $purchaseOrder->id,
                                'part_id' => $part->id,
                                'act_cost' => $faker->randomFloat(2, 0, 100),
                                'qty' => random_int(2, 6),
                            ]));

                            return;
                        }
                        case 'bins': {
                            $part->bins()->saveMany(factory(BinQuantity::class, 2)->make());
                        }
                    }
                }
            });
            Model::reguard();
        }
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        // Database clean up
        Part::where('dealer_id', $dealerId)->delete();
        Vendor::where('dealer_id', $dealerId)->delete();
        CacheStoreTime::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }

    public function getDealerId(): int
    {
        return $this->dealer->getKey();
    }
}
