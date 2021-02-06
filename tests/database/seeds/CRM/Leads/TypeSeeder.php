<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read Lead $lead
 * @property-read array<LeadType> $missingTypes
 * @property-read array<LeadType> $createdTypes
 */
class TypeSeeder extends Seeder
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
     * @var LeadType[]
     */
    private $missingTypes = [];

    /**
     * @var LeadType[]
     */
    private $createdTypes = [];

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
            ['lead_type' => LeadType::TYPE_GENERAL],
            ['lead_type' => LeadType::TYPE_INVENTORY],
            ['lead_type' => LeadType::TYPE_CRAIGSLIST],
            ['lead_type' => LeadType::TYPE_TEXT, 'action' => 'create'],
            ['lead_type' => LeadType::TYPE_BUILD, 'action' => 'create'],
            ['lead_type' => LeadType::TYPE_FINANCING, 'action' => 'create']
        ];

        collect($seeds)->each(function (array $seed) use($leadId): void {
            // Create Type
            if(isset($seed['action']) && $seed['action'] === 'create') {
                // Make Type
                $type = factory(LeadType::class)->create([
                    'lead_id' => $leadId,
                    'lead_type' => $seed['lead_type']
                ]);

                $this->createdTypes[] = $type;
                return;
            }

            // Make Type
            $type = factory(LeadType::class)->make([
                'lead_id' => $leadId,
                'lead_type' => $seed['lead_type']
            ]);

            $this->missingTypes[] = $type;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $leadId = $this->lead->getKey();

        // Database clean up
        LeadType::where('lead_id', $leadId)->delete();
        Lead::destroy($leadId);
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();                
        User::destroy($dealerId);
    }
}