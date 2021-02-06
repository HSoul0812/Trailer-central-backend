<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;
use Illuminate\Support\Str;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read Lead $lead
 * @property-read array<InventoryLead> $missingUnits
 * @property-read array<InventoryLead> $createdUnits
 */
class UnitSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var Lead
     */
    private $lead;

    /**
     * @var Inventory[]
     */
    private $unrelatedInventories = [];

    /**
     * @var Inventory[]
     */
    private $leadRelatedInventories = [];

    /**
     * @var InventoryLead[]
     */
    private $leadInventoryIds = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->lead = factory(Lead::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey()
        ]);
    }

    public function seed(): void
    {
        $leadId = $this->lead->getKey();

        $seeds = [
            ['title' => 'SOLD 2013 SOONER 3 HORSE WITH DRESS RM1'],
            ['title' => '2020 Adirondack j6i66i Snowmobile Trailer'],
            ['title' => '2021 Adirondack G6i77j Snowmobile Trailer'],
            ['title' => 'Windsurf board Magic Wave PRO'],
            ['title' => '2017 Adventure Sports Products Adventure Testing Horse Trailer'],
            ['title' => '102 Ironworks Dump Truck', 'lead_id' => $leadId],
            ['title' => '103 Ironworks Dump Truck', 'lead_id' => $leadId],
            ['title' => 'Wayland Dump Truck', 'lead_id' => $leadId]
        ];

        collect($seeds)->each(function (array $seed): void {
            $inventory = factory(Inventory::class)->create([
                'dealer_id' => $this->dealer->getKey(),
                'title' => $seed['title'],
                'vin' => Str::random(18)
            ]);

            $inventoryId = $inventory->getKey();

            if (isset($seed['lead_id'])) {
                $leadInventory = InventoryLead::create(['website_lead_id' => $seed['lead_id'], 'inventory_id' => $inventoryId]);
                $this->leadRelatedInventories[] = $inventory;
                $this->leadInventoryIds[] = $leadInventory->getKey();

                return;
            }

            $this->unrelatedInventories[] = $inventory;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $leadId = $this->lead->getKey();

        // Database clean up
        InventoryLead::where('website_lead_id', $leadId)->delete();
        Lead::destroy($leadId);
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();                
        User::destroy($dealerId);
    }
}