<?php

declare(strict_types=1);

namespace Tests\database\seeds\Part;

use App\Models\Parts\Bin;
use App\Models\Parts\CycleCount;
use App\Models\User\User;
use App\Traits\WithGetter;
use App\Models\User\AuthToken;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;
use App\Models\User\DealerLocation;

/**
 * @property-read User $dealer
 */
class BinSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var AuthToken
     */
    private $authToken;

    public $bins;

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
    }

    public function seedLocation()
    {
        return factory(DealerLocation::class)->create([
            'latitude' => 11,
            'longitude' => 11,
            'dealer_id' => $this->dealer->dealer_id,
        ]);
    }

    public function seed(int $count = 10): void
    {
        $dealerId = $this->dealer->getKey();
        $faker = Faker::create();

        $location = $this->seedLocation();

        $this->bins = factory(Bin::class, $count)->create([
            'dealer_id' => $dealerId,
            'location' => $location->dealer_location_id,
            'bin_name' => $faker->sentence()
        ]);

        $this->bins->each(function ($bin) {
            CycleCount::create([
                'id' => $bin->location, // i dont know why?? check `cycle_count_exists` validation rule.
                'dealer_id' => $bin->dealer_id,
                'bin_id' => $bin->id,
                'count_date' => now(),
                'is_completed' => false,
                'is_balanced' => false
            ]);
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        Bin::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }

    public function getDealerId(): int
    {
        return $this->dealer->getKey();
    }

    public function getAccessToken()
    {
        return $this->authToken->access_token;
    }
}
