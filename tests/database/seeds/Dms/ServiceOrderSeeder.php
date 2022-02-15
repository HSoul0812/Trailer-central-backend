<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms;

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read AuthToken $authToken
 * @property-read ServiceOrder[] $serviceOrders
 */
class ServiceOrderSeeder extends Seeder
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

    /**
     * @var ServiceOrder[]
     */
    private $serviceOrders;

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

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        foreach (range(0, $faker->numberBetween(10, 20)) as $number) {
            $this->serviceOrders[] = factory(ServiceOrder::class)->create(['dealer_id' => $dealerId]);
        }
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        // Database clean up
        ServiceOrder::where('dealer_id', $dealerId)->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($dealerId);
    }

    public function getDealerId(): int
    {
        return $this->dealer->getKey();
    }
}
