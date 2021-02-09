<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms;

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 */
class ServiceOrderSeeder extends Seeder
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
            factory(ServiceOrder::class)->create(['dealer_id' => $dealerId]);
        }
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        // Database clean up
        ServiceOrder::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }

    public function getDealerId(): int
    {
        return $this->dealer->getKey();
    }
}
