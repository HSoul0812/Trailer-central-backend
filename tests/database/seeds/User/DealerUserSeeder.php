<?php

declare(strict_types=1);

namespace Tests\database\seeds\User;

use App\Models\User\DealerUser;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read DealerUser $dealeruser
 */
class DealerUserSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    protected $dealer;

    /**
     * @var DealerUser
     */
    protected $dealerUser;

    public function seed(): void
    { 
      
      
        $this->seedDealer();

        $dealerId = $this->dealer->getKey();

        $dealerUserParams = [
          'dealer_id' => $dealerId, 
          'user_permissions' => 'ecommerce'
        ];

        $this->dealerUser = factory(DealerUser::class, 1)->create(['dealer_id' => $dealerId]); // 1 new dealerUser
    
    }

    public function seedDealer(): void
    {
        $this->dealer = factory(User::class)->create();
    }

    public function cleanUp(): void
    {
        // Database clean up
        User::destroy($this->dealer->dealer_id);
        DealerUser::where('dealer_id', $this->dealer->dealer_id)->delete();
    }
}