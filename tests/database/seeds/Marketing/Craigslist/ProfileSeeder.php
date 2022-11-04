<?php

namespace Tests\database\seeds\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Profile;
use App\Models\User\AuthToken;
use App\Models\User\DealerLocation;
use App\Models\User\DealerClapp;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read CrmUser $user
 * @property-read DealerLocation $dealerLocation
 * @property-read AuthToken $authToken
 * @property-read Profile[] $profiles
 */
class ProfileSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var DealerClapp
     */
    private $clapp;

    /**
     * @var DealerLocation
     */
    private $dealerLocation;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var Profile[]
     */
    private $profiles = [];


    /**
     * ProfileSeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->clapp = factory(DealerClapp::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->dealerLocation = factory(DealerLocation::class)->create([
            'latitude' => 11,
            'longitude' => 11,
            'dealer_id' => $this->dealer->dealer_id,
        ]);

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
    }

    public function seed(): void
    {
        $seeds = [
            [], [], [], []
        ];

        collect($seeds)->each(function (array $seed): void {
            $this->profiles[] = factory(Profile::class)->create([
                'dealer_id' => $this->dealer->getKey(),
            ]);
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        DealerLocation::where('dealer_id', $dealerId)->delete();
        DealerClapp::where('dealer_id', $dealerId)->delete();
        Profile::where('dealer_id', $dealerId)->delete();
        AuthToken::where('user_id', $dealerId)->delete();

        User::destroy($dealerId);
    }
}
