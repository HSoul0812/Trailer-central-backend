<?php

use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\User\SalesPerson;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\User\NewDealerUser;
use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LeadSeeder extends Seeder
{
    private const DEALER_ID = 1001;
    private const COUNT_OF_LEADS = [50, 100];

    public function run(): void
    {
        $this->clean();

        $countOfLeads = rand(self::COUNT_OF_LEADS[0], self::COUNT_OF_LEADS[1]);

        /** @var Website $website */
        $website = Website::query()->where('dealer_id', '=', self::DEALER_ID)->first();

        /** @var NewDealerUser $newDealerUser */
        $newDealerUser = NewDealerUser::query()->where('id', '=', self::DEALER_ID)->first();

        /** @var <Collection> SalesPerson $salesPeople */
        $salesPeople = SalesPerson::query()->where('user_id', '=', $newDealerUser->user_id)->get();

        $inventories = Inventory::query()->where('dealer_id', '=', self::DEALER_ID)->get();

        /** @var <Collection>DealerLocation $dealerLocations */
        $dealerLocations = DealerLocation::query()
            ->where('dealer_id', '=', self::DEALER_ID)
            ->whereNull('deleted_at')
            ->where('show_on_website_locations', '=', 1)
            ->get();

        for ($i = 0; $i < $countOfLeads; $i++) {
            /** @var DealerLocation $dealerLocation */
            $dealerLocation = $dealerLocations->random();

            /** @var Inventory $inventory */
            $inventory = $inventories->random();

            $leadType = LeadType::TYPE_ARRAY[array_rand(LeadType::TYPE_ARRAY)];
            $isArchived = rand(1, 10) === 10;

            /** @var Lead $lead */
            $lead = factory(Lead::class)->create([
                'dealer_id' => self::DEALER_ID,
                'website_id' => $website->id,
                'dealer_location_id' => $dealerLocation->dealer_location_id,
                'lead_type' => $leadType,
                'is_archived' => $isArchived,
                'inventory_id' => $inventory->inventory_id,
                'date_submitted' => Carbon::today()->subDays(rand(0, 365))->subHours(rand(0, 24))->subMinutes(rand(0, 60))->subSeconds(rand(0, 60))
            ]);

            factory(LeadType::class)->create([
                'lead_id' => $lead->identifier
            ]);

            if (rand(0, 1)) {
                $salesPerson = $salesPeople->random();

                factory(LeadStatus::class)->create([
                    'tc_lead_identifier' => $lead->identifier,
                    'sales_person_id' => $salesPerson->id,
                ]);
            }

            /** @var Inventory $inventory */
            $inventory = $inventories->random();

            factory(InventoryLead::class)->create([
                'website_lead_id' => $lead->identifier,
                'inventory_id' => $inventory->inventory_id,
            ]);
        }
    }

    private function clean()
    {
        /** @var Website $website */
        $website = Website::query()->where('dealer_id', '=', self::DEALER_ID)->first();

        $leads = Lead::query()
            ->where('dealer_id', '=', self::DEALER_ID)
            ->orWhere('website_id', '=', $website->id)
            ->get();

        foreach ($leads as $lead) {
            LeadType::query()->where('lead_id', '=', $lead->identifier)->delete();
            InventoryLead::query()->where('website_lead_id', '=', $lead->identifier)->delete();
            $lead->delete();
        }
    }
}
