<?php

declare(strict_types=1);

namespace Tests\database\seeds\Part;

use App\Models\Parts\Part;
use App\Models\Parts\CacheStoreTime;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;

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
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
    }

    public function seed(): void
    {
        $dealerId = $this->dealer->getKey();
        $faker = Faker::create();

        foreach (range(0, $faker->numberBetween(10, 20)) as $number) {
            factory(Part::class)->create([
                'dealer_id' => $dealerId,
                'dealer_cost' => $faker->randomFloat(2, 0, 50),
                'latest_cost' => $faker->randomFloat(2, 0, 50)
            ]);
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
