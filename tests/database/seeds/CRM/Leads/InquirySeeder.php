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
        $this->lead = factory(Lead::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey(),
            'lead_type' => LeadType::TYPE_GENERAL
        ]);

        $this->website->websiteConfigs()->updateOrCreate(['key' => WebsiteConfig::LEADS_MERGE_ENABLED], ['value' => 1]);
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        Lead::destroy($this->lead->getKey());
        DealerLocation::where('dealer_id', $dealerId)->delete();
        WebsiteConfig::where('website_id', $this->website->getKey())->delete();
        Website::where('dealer_id', $dealerId)->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($dealerId);
        NewUser::destroy($dealerId);
        NewDealerUser::destroy($dealerId);
        CrmUser::destroy($dealerId);
        User::destroy($dealerId);
    }
}