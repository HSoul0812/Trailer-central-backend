<?php

namespace Tests\database\seeds\CRM\Interactions;

use Tests\database\seeds\Seeder;
use App\Traits\WithGetter;
use App\Models\User\User;
use App\Models\User\DealerUser;
use App\Models\User\DealerUserPermission;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Interactions\Interaction;
use App\Models\User\AuthToken;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Models\User\Interfaces\PermissionsInterface;

class InteractionSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var NewUser
     */
    private $user;

    private $leads;

    /**
     * @var SalesPerson
     */
    private $salesPerson;

    /**
     * @var array
     */
    private $dealerInteractions;

    /**
     * @var AuthToken
     */
    private $authToken;

    private $dealerUser;

    private $salesPersonAuthToken;

    private $salesPersonLeads;

    private $salesPersonInteractions;

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();

        $this->user = factory(NewUser::class)->create();

        // 
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);

        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $this->user->user_id,
            'salt' => md5((string)$this->user->user_id), // random string
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);

        $this->dealer->newDealerUser()->save($newDealerUser);

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
        

        $salesPerson = factory(SalesPerson::class)->create([
            'user_id' => $this->user->user_id
        ]);

        // START setup SalesPerson & it's AuthToken

        $this->salesPerson = $salesPerson;

        $this->dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->dealer->dealer_id
        ]);

        factory(DealerUserPermission::class)->create([
            'dealer_user_id' => $this->dealerUser->dealer_user_id,
            'feature' => PermissionsInterface::CRM,
            'permission_level' => $this->salesPerson->getKey()
        ]);

        $this->salesPersonAuthToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealerUser->dealer_user_id,
            'user_type' => AuthToken::USER_TYPE_DEALER_USER,
        ]);

        // END setup SalesPerson

        $salesPersonInteractions = [];
        // Leads with SalesPerson assigned
        $this->salesPersonLeads = factory(Lead::class, 5)->create(['dealer_id' => $this->dealer->dealer_id])
            ->each(function($lead) use (&$salesPersonInteractions, $salesPerson) {

            factory(LeadStatus::class)->create([
                'contact_type' => LeadStatus::TYPE_CONTACT,
                'tc_lead_identifier' => $lead->getKey(),
                'sales_person_id' => $salesPerson->getKey()
            ]);

            $salesPersonInteractions[] = factory(Interaction::class)->create([
                'tc_lead_id' => $lead->getKey(),
                'interaction_type' => Interaction::TYPE_CONTACT,
                'user_id' => $salesPerson->user_id,
                'sales_person_id' => $salesPerson->getKey()
            ]);
        });
        
        $this->salesPersonInteractions = $salesPersonInteractions;
        
        $dealerInteractions = []; 
        // Leads without SalesPerson assigned
        $this->leads = factory(Lead::class, 5)->create(['dealer_id' => $this->dealer->dealer_id])
            ->each(function($lead) use (&$dealerInteractions, $salesPerson) {

            factory(LeadStatus::class)->create([
                'contact_type' => LeadStatus::TYPE_CONTACT,
                'tc_lead_identifier' => $lead->getKey(),
                'sales_person_id' => NULL
            ]);

            $dealerInteractions[] = factory(Interaction::class)->create([
                'tc_lead_id' => $lead->getKey(),
                'interaction_type' => Interaction::TYPE_CONTACT,
                'user_id' => $salesPerson->user_id
            ]);
        });

        $this->dealerInteractions = $dealerInteractions;
    }

    public function cleanUp(): void
    {
        Interaction::where('user_id', $this->user->user_id)->delete();
        $this->leads->each(function($lead) {
                $lead->leadStatus()->delete();
                $lead->delete();
            });

        $this->salesPersonLeads->each(function($lead) {
            $lead->leadStatus()->delete();
            $lead->delete();
        });

        $this->salesPersonAuthToken->delete();
        $this->dealerUser->perms()->delete();
        $this->dealerUser->delete();
        $this->salesPerson->delete();

        $this->authToken->delete();
        NewDealerUser::where('user_id', $this->user->user_id)->delete();
        $this->user->delete();
        $this->dealer->delete();
    }
}