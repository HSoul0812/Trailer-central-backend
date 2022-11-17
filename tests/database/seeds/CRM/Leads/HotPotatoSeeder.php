<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\User\SalesPerson;
use App\Models\Inventory\Inventory;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Models\User\CrmUser;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;
use Carbon\Carbon;

/**
 * @property-read User $dealer
 * @property-read DealerLocation $location
 * @property-read DealerLocation $location2
 * @property-read NewDealerUser $newDealer
 * @property-read CrmUser $crmUser
 * @property-read AuthToken $authToken
 * @property-read Website $website
 * @property-read SalesPerson $salesPerson
 * @property-read SalesPerson[] $sales
 * @property-read Lead[] $leads
 * @property-read LeadStatus[] $statuses
 * @property-read array<Lead> $leads
 * @property-read array<LeadStatus> $statuses
 */
class HotPotatoSeeder extends Seeder
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
     * @var DealerLocation
     */
    private $location2;

    /**
     * @var Inventory
     */
    private $inventory;

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
     * @var SalesPerson
     */
    private $salesPerson;

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
        $this->location2 = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->dealer_id
        ]);
        $this->inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->dealer->dealer_id,
            'dealer_location_id' => $this->location2
        ]);
        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->user = factory(NewUser::class)->create();
        $this->newDealer = factory(NewDealerUser::class)->create(['id' => $this->dealer->dealer_id, 'user_id' => $this->user->getKey()]);
        $this->crmUser = factory(CrmUser::class)->create(['user_id' => $this->user->getKey(), 'enable_hot_potato' => 1]);

        // Create Default Sales Person
        $this->salesPerson = factory(SalesPerson::class)->create([
            'user_id' => $this->user->getKey(),
            'dealer_location_id' => $this->location->getKey(),
            'is_default' => 1,
            'is_inventory' => 1,
            'is_trade' => 1,
            'is_financing' => 1
        ]);
    }

    public function enableAssignEmail($enabled = 1): void
    {
        $this->crmUser->fill(['enable_assign_notification' => $enabled])->save();
    }

    public function seed(): void
    {
        // Seed Leads for Hot Potato
        $locationId = $this->location->getKey();
        $seeds = [
            ['source' => 'TruckPaper', 'type' => 'inventory', 'dealer_location_id' => $locationId],
            ['source' => 'Facebook - Podium', 'type' => 'trade', 'dealer_location_id' => $locationId],
            ['source' => 'Google', 'type' => 'inventory', 'dealer_location_id' => 0],
            ['source' => 'RVTrader.com', 'type' => 'trade', 'dealer_location_id' => $locationId],
            ['source' => 'TrailerCentral', 'type' => 'inventory', 'dealer_location_id' => 0],
            ['type' => 'trade', 'dealer_location_id' => 0],
            ['source' => 'Facebook - Marketplace', 'type' => 'inventory', 'dealer_location_id' => $locationId],
            ['source' => 'HorseTrailerWorld', 'type' => 'inventory', 'dealer_location_id' => $locationId]
        ];

        $this->leads($seeds);

        // Create Sales People for Hot Potato
        $salesSeeds = [
            ['dealer_location_id' => 0, 'is_inventory' => 0],
            ['is_inventory' => 0],
            ['is_trade' => 0],
            ['dealer_location_id' => 0, 'is_trade' => 0]
        ];
        $this->sales($salesSeeds);
    }

    public function seedNoMatches(): void
    {
        // Seed Leads for Hot Potato
        $locationId = $this->location->getKey();
        $seeds = [
            ['source' => 'TruckPaper', 'type' => 'inventory', 'dealer_location_id' => $locationId],
            ['source' => 'Facebook - Podium', 'type' => 'trade', 'dealer_location_id' => $locationId],
            ['source' => 'Google', 'type' => 'inventory', 'dealer_location_id' => 0],
            ['source' => 'RVTrader.com', 'type' => 'financing', 'dealer_location_id' => $locationId],
            ['source' => 'TrailerCentral', 'type' => 'inventory', 'dealer_location_id' => 0],
            ['type' => 'trade', 'dealer_location_id' => 0],
            ['source' => 'Facebook - Marketplace', 'type' => 'financing', 'dealer_location_id' => $locationId],
            ['source' => 'HorseTrailerWorld', 'type' => 'inventory', 'dealer_location_id' => $locationId]
        ];

        $this->leads($seeds);

        // Create Sales People for Hot Potato
        $salesSeeds = [
            ['dealer_location_id' => 0, 'is_inventory' => 0],
            ['is_inventory' => 0],
            ['is_trade' => 0],
            ['dealer_location_id' => 0, 'is_trade' => 0]
        ];
        $this->sales($salesSeeds);
    }

    public function seedWithUnits(): void
    {
        // Seed Leads for Hot Potato
        $locationId = $this->location->getKey();
        $inventoryId = $this->inventory->getKey();
        $seeds = [
            ['source' => 'TruckPaper', 'type' => 'inventory', 'dealer_location_id' => $locationId],
            ['source' => 'Facebook - Podium', 'type' => 'trade', 'dealer_location_id' => $locationId],
            ['source' => 'Google', 'type' => 'inventory', 'dealer_location_id' => 0, 'inventory_id' => $inventoryId],
            ['source' => 'RVTrader.com', 'type' => 'trade', 'dealer_location_id' => $locationId],
            ['source' => 'TrailerCentral', 'type' => 'inventory', 'dealer_location_id' => 0],
            ['type' => 'trade', 'dealer_location_id' => 0],
            ['source' => 'Facebook - Marketplace', 'type' => 'inventory', 'dealer_location_id' => 0, 'inventoryId' => $inventoryId],
            ['source' => 'HorseTrailerWorld', 'type' => 'inventory', 'dealer_location_id' => $locationId]
        ];

        $this->leads($seeds);

        // Create Sales People for Hot Potato
        $salesSeeds = [
            ['dealer_location_id' => $this->location2->getKey(), 'is_trade' => 0],
            ['dealer_location_id' => 0, 'is_inventory' => 0],
            ['is_inventory' => 0],
            ['is_trade' => 0],
            ['dealer_location_id' => 0, 'is_trade' => 0]
        ];
        $this->sales($salesSeeds);
    }

    private function leads($seeds): void
    {
        // Set Default Lead Params
        $params = [
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey()
        ];

        collect($seeds)->each(function (array $seed) use($params): void
        {
            // Set Lead Type
            if(isset($seed['type'])) {
                $params['lead_type'] = $seed['type'];
            }

            // Set Location
            if(isset($seed['dealer_location_id'])) {
                $params['dealer_location_id'] = $seed['dealer_location_id'];
            }

            // Create Lead
            $params['date_submitted'] = Carbon::now()->subDays(7)->toDateTimeString();
            $lead = factory(Lead::class)->create($params);
            $leadId = $lead->getKey();
            $this->leads[] = $lead;


            // Make Status
            $this->statuses[$leadId] = factory(LeadStatus::class)->create([
                'tc_lead_identifier' => $leadId,
                'source' => $seed['source'] ?? '',
                'next_contact_date' => Carbon::now()->subMinutes(45)->toDateTimeString(),
                'sales_person_id' => $this->salesPerson->getKey()
            ]);
        });
    }

    private function sales($seeds): void
    {
        // Initialize Sales People Seeds
        $params = [
            'user_id' => $this->user->getKey(),
            'dealer_location_id' => $this->location->getKey(),
            'is_default' => 1,
            'is_inventory' => 1,
            'is_trade' => 1,
            'is_financing' => 0
        ];

        // Loop Seeds for Sales People
        collect($seeds)->each(function (array $seed) use($params): void {
            $this->sales[] = factory(SalesPerson::class)->create(array_merge($params, $seed));
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->user->getKey();

        // Database clean up
        if(!empty($this->leads) && count($this->leads)) {
            foreach($this->leads as $lead) {
                $leadId = $lead->identifier;
                LeadStatus::where('tc_lead_identifier', $leadId)->delete();
                Lead::destroy($leadId);
            }
        }
        LeadAssign::where(['dealer_id' => $dealerId])->delete();

        // Clear Out CRM User Data
        NewUser::destroy($userId);
        SalesPerson::where(['user_id' => $userId])->delete();
        CrmUser::where('user_id', $userId)->delete();

        // Clear Out User Data
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        NewDealerUser::destroy($dealerId);
        User::destroy($dealerId);
    }
}
