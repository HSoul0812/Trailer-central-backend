<?php

declare(strict_types=1);

namespace Tests\database\seeds\User;

use App\Models\User\DealerPart;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read array<DealerPart> $dealerPart
 */
class DealerPartSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    protected $dealer;

    /**
     * @var DealerPart[]
     */
    protected $dealerPart;

    public function seed(): void
    {
        $this->seedDealer();


        $dealerId = $this->dealer->getKey();


        $this->dealerPart = factory(DealerPart::class)->create(['dealer_id' => $dealerId]); // 1 new dealaerPart
    }

    public function seedDealer(): void
    {
        $this->dealer = factory(User::class)->create();
    }

    public function cleanUp(): void
    {
        // Database clean up

        User::whereIn('dealer_id', $this->dealer->getKey())->delete();
    }
}
