<?php

namespace Tests\database\seeds\CRM\Leads;

use Tests\database\seeds\Seeder;
use App\Models\User\User;
use App\Models\User\AuthToken;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use App\Models\User\NewUser;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\Inventory\Inventory;

class ADFSeeder extends Seeder {

    use WithGetter;

    /**
     * @var User
     */
    protected $dealer;

    /**
     * @var DealerLocation
     */
    protected $location;

    /**
     * @var Website
     */
    protected $website;

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();

        /**
         * necessary data for dealer
         */
        $user = factory(NewUser::class)->create();
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $user->user_id,
            'salt' => md5((string)$user->user_id), // random string
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);
        $this->dealer->newDealerUser()->save($newDealerUser);
        $crmUserRepo = app(CrmUserRepositoryInterface::class);
        $crmUserRepo->create([
            'user_id' => $user->user_id,
            'logo' => '',
            'first_name' => '',
            'last_name' => '',
            'display_name' => '',
            'dealer_name' => $this->dealer->name,
            'active' => 1
        ]);
        // END

        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->getKey()]);

        $this->location = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->getKey(),
        ]);
    }

    public function cleanUp(): void
    {
        Lead::where(['dealer_id' => $this->dealer->getKey()])->delete();
        Website::destroy($this->website->getKey());
        Inventory::where(['dealer_id' => $this->dealer->getKey()])->delete();
        DealerLocation::where(['dealer_id' => $this->dealer->getKey()])->delete();

        AuthToken::where(['user_id' => $this->dealer->getKey(), 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        NewUser::destroy($this->dealer->getKey());
        NewDealerUser::destroy($this->dealer->getKey());
        CrmUser::destroy($this->dealer->getKey());
        User::destroy($this->dealer->getKey());
    }
}