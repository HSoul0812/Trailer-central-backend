<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Models\User\CrmUser;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read SalesPerson $sales
 * @property-read AuthToken $authToken
 * @property-read array<Lead> $leads
 * @property-read array<LeadStatus> $statuses
 */
class AutoAssignSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var DealerLocation
     */
    private $location;

    /**
     * @var NewDealerUser
     */
    private $newDealer;

    /**
     * @var CrmUser
     */
    private $crmUser;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var SalesPerson[]
     */
    private $sales = [];

    /**
     * @var Lead[]
     */
    private $leads = [];

    /**
     * @var LeadStatus[]
     */
    private $statuses = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->location = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->dealer_id
        ]);
        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->user = factory(NewUser::class)->create(['user_id' => $this->dealer->dealer_id]);
        $this->newDealer = factory(NewDealerUser::class)->create(['id' => $this->dealer->dealer_id, 'user_id' => $this->dealer->dealer_id]);
        $this->crmUser = factory(CrmUser::class)->create(['user_id' => $this->dealer->dealer_id, 'enable_assign_notification' => 1]);
    }

    public function seed(): void
    {
        // Seed Leads for Auto Assign
        $locationId = $this->location->getKey();
        $seeds = [
            ['type' => 'inventory', 'dealer_location_id' => $locationId],
            ['source' => 'Facebook - Podium', 'type' => 'trade', 'dealer_location_id' => $locationId],
            ['source' => '', 'type' => 'inventory', 'dealer_location_id' => 0],
            ['source' => 'RVTrader.com', 'type' => 'trade', 'dealer_location_id' => $locationId],
            ['source' => 'TrailerCentral', 'type' => 'inventory', 'dealer_location_id' => 0],
            ['type' => 'inventory', 'dealer_location_id' => 0],
            ['source' => 'HorseTrailerWorld', 'type' => 'inventory', 'dealer_location_id' => $locationId],
            ['source' => '', 'type' => 'trade', 'dealer_location_id' => $locationId]
        ];

        $this->leads($seeds);


        // Seed Sales People for Auto Assign
        $salesSeeds = [
            ['dealer_location_id' => 0, 'is_inventory' => 0],
            ['is_inventory' => 0],
            ['is_trade' => 0],
            ['dealer_location_id' => 0, 'is_trade' => 0]
        ];

        $this->sales($salesSeeds);
    }

    private function leads($seeds): void {
        collect($seeds)->each(function (array $seed): void {
            $leadParams = [
                'dealer_id' => $this->dealer->getKey(),
                'website_id' => $this->website->getKey()
            ];
            if(isset($seed['type'])) {
                $leadParams['lead_type'] = $seed['type'];
            }
            $lead = factory(Lead::class)->create($leadParams);
            $leadId = $lead->getKey();
            $this->leads[] = $lead;

            // Include Status
            if(isset($seed['source'])) {
                // Make Status
                $status = factory(LeadStatus::class)->make([
                    'tc_lead_identifier' => $leadId,
                    'source' => $seed['source']
                ]);

                $this->statuses[$leadId] = $status;
            }
        });
    }

    private function sales($seeds): void {
        // Initialize Sales People Seeds
        $params = [
            'user_id' => $this->dealer->getKey(),
            'dealer_location_id' => $this->location->getKey(),
            'is_default' => 1,
            'is_inventory' => 1,
            'is_trade' => 1
        ];

        // Loop Seeds for Sales People
        collect($seeds)->each(function (array $seed) use($params): void {
            $this->sales[] = factory(SalesPerson::class)->create(array_merge($params, $seed));
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        var_dump($dealerId);

        // Database clean up
        if(!empty($this->leads) && count($this->leads)) {
            foreach($this->leads as $lead) {
                $leadId = $lead->identifier;
                LeadStatus::where('tc_lead_identifier', $leadId)->delete();
                Lead::destroy($leadId);
            }
        }
        LeadAssign::where(['dealer_id' => $dealerId])->delete();

        // Clear Out User Data
        SalesPerson::where(['user_id' => $dealerId])->delete();
        CrmUser::destroy($dealerId);
        NewUser::destroy($dealerId);
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        NewDealerUser::destroy($dealerId);
        User::destroy($dealerId);
    }
}
