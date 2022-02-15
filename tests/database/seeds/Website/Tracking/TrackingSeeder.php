<?php

declare(strict_types=1);

namespace Tests\database\seeds\Website\Tracking;

use App\Models\CRM\Leads\Lead;
use App\Models\Website\Website;
use App\Models\Website\Tracking\Tracking;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read array<Lead> $leads
 * @property-read array<Tracking> $createdTracking
 * @property-read array<Tracking> $missingLeadTracking
 */
class TrackingSeeder extends Seeder
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
     * @var Leads[]
     */
    private $leads = [];

    /**
     * @var Tracking[]
     */
    private $createdTracking = [];

    /**
     * @var Tracking[]
     */
    private $missingLeadTracking = [];

    /**
     * TrackingSeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->getKey()]);
    }

    public function seed(): void
    {
        $dealerId = $this->dealer->getKey();
        $websiteId = $this->website->getKey();

        $seeds = [
            ['with_lead' => false],
            ['with_lead' => false],
            ['with_lead' => false],
            ['with_lead' => false],
            ['with_lead' => true],
            ['with_lead' => true],
            ['with_lead' => true]
        ];

        collect($seeds)->each(function (array $seed) use($dealerId, $websiteId): void {
            // Create Lead
            $lead = factory(Lead::class)->create([
                'dealer_id' => $dealerId,
                'website_id' => $websiteId
            ]);
            $this->leads[$lead->getKey()] = $lead;

            // Create Tracking
            $tracking = factory(Tracking::class)->create([
                'domain' => $this->website->domain,
                'lead_id' => !empty($seed['with_lead']) ? $lead->getKey() : null
            ]);

            // Add to Created Tracking?
            if(!empty($seed['with_lead'])) {
                $this->createdTracking[] = $tracking;
            } else {
                $tracking->fill(['lead_id' => $lead->getKey()]);
                $this->missingLeadTracking[] = $tracking;
            }
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $websiteId = $this->dealer->getKey();

        // Delete All Tracking Entries
        foreach($this->createdTracking as $tracking) {
            $tracking->delete();
        }
        foreach($this->missingLeadTracking as $tracking) {
            $tracking->delete();
        }

        // Database clean up
        Lead::where('dealer_id', $dealerId)->delete();
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::destroy($websiteId);
        User::destroy($dealerId);
    }
}
