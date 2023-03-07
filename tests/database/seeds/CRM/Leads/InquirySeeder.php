<?php

namespace Tests\database\seeds\CRM\Leads;

use Tests\database\seeds\Seeder;
use App\Models\CRM\Leads\Lead;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Models\Website\Config\WebsiteConfig;
use App\Traits\WithGetter;
use App\Models\User\NewUser;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Models\CRM\Interactions\Interaction;
use App\Models\Inventory\Inventory;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\User\Customer;

class InquirySeeder extends Seeder {

    use WithGetter;
    /**
     * @var User
     */
    protected $dealer;

    /**
     * @var AuthToken
     */
    protected $authToken;

    /**
     * @var Website
     */
    protected $website;

    /**
     * @var Lead
     */
    protected $lead;

    /**
     * @var Customer
     */
    protected $customer;

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();

        /**
         * Unknown necessary data for dealer
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

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);

        $location = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->getKey(),
        ]);

        $this->lead = factory(Lead::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey(),
            'lead_type' => LeadType::TYPE_GENERAL,
            'dealer_location_id' => $location->getKey()
        ]);

        $this->anotherInventory = factory(Inventory::class)->create([
            'dealer_id' => $this->dealer->dealer_id,
            'dealer_location_id' => $this->lead->dealer_location_id
        ]);

        $this->website->websiteConfigs()->updateOrCreate(['key' => WebsiteConfig::LEADS_MERGE_ENABLED], ['value' => 1]);

        $this->customer = factory(Customer::class)->create([
            'dealer_id' => $this->dealer->getKey()
        ]);
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->dealer->newDealerUser->user_id;

        Customer::where('dealer_id', $dealerId)->forceDelete();
        Interaction::where('tc_lead_id', $this->lead->getKey())->delete();
        InventoryLead::where('website_lead_id', $this->lead->getKey())->delete();
        Lead::where('dealer_id', $dealerId)->delete();
        DealerLocation::where('dealer_id', $dealerId)->delete();
        WebsiteConfig::where('website_id', $this->website->getKey())->delete();
        Website::where('dealer_id', $dealerId)->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();

        CrmUser::where('user_id', $userId)->delete();
        NewUser::destroy($userId);
        NewDealerUser::destroy($dealerId);
        User::destroy($dealerId);

        CrmUser::where('user_id', $userId)->delete();
        NewUser::destroy($userId);
        NewDealerUser::destroy($dealerId);
    }
}
