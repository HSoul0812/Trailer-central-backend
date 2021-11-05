<?php

declare(strict_types=1);

namespace Tests\database\seeds\Ecommerce;

use App\Models\Parts\Textrail\Part;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read CompletedOrder $completedOrder
 * @property-read AuthToken $authToken
 */
class CompletedOrderSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var Part
     */
    protected $part;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var CompletedOrder
     */
    protected $completedOrder;

    /**
     * CompletedOrderSeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
    }

    public function seed(): void
    {
        $this->seedPart();

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->completedOrder = factory(CompletedOrder::class, 1)->create(['dealer_id' => $this->dealer->dealer_id]); // 1 new completed order
    }

    public function seedPart(): void
    {
        $this->part = factory(Part::class, 1)->create([
            "manufacturer_id" => 66,
            "brand_id" => 25,
            "type_id" => 11,
            "category_id" => 8,
        ]);
    }

    public function cleanUp(): void
    {
        // Database clean up
        CompletedOrder::whereIn('id', $this->completedOrder->getKey())->delete();
        Part::whereIn('id', $this->part->getKey())->delete();

        $dealerId = $this->dealer->getKey();

        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($dealerId);
    }
}
