<?php

declare(strict_types=1);

namespace Tests\database\seeds\Website\Tracking;

use App\Models\CRM\Leads\Lead;
use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use App\Models\Website\Website;
use App\Models\Website\Tracking\Tracking;
use App\Models\Website\Tracking\TrackingUnit;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read Lead $lead
 * @property-read Tracking $tracking
 * @property-read Inventory $inventory
 * @property-read Part $parts
 * @property-read array<TrackingUnit> $units
 */
class TrackingUnitSeeder extends Seeder
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
     * @var Tracking
     */
    private $tracking;

    /**
     * @var Inventory[]
     */
    private $inventory = [];

    /**
     * @var Part[]
     */
    private $parts = [];

    /**
     * @var TrackingUnit[]
     */
    private $units = [];

    /**
     * TrackingUnitSeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->getKey()]);
        $this->lead = factory(Lead::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey()
        ]);
        $this->tracking = factory(Tracking::class)->create([
            'domain' => $this->website->domain,
            'lead_id' => $this->lead->getKey()
        ]);
    }

    public function seed(): void
    {
        $dealerId = $this->dealer->getKey();
        $sessionId = $this->tracking->session_id;

        $seeds = [
            ['type' => 'inventory', 'item' => 0],
            ['type' => 'inventory', 'item' => 1],
            ['type' => 'part', 'item' => 0],
            ['type' => 'part', 'item' => 1],
            ['type' => 'part', 'item' => 0],
            ['type' => 'inventory', 'item' => 1],
            ['type' => 'part', 'item' => 2],
            ['type' => 'part', 'item' => 0],
            ['type' => 'inventory', 'item' => 2]
        ];

        collect($seeds)->each(function (array $seed) use($sessionId, $dealerId): void {
            // Create Inventory/Part
            if($seed['type'] === 'part') {
                if(!isset($this->parts[$seed['item']])) {
                    $this->parts[] = factory(Part::class)->create([
                        'dealer_id' => $dealerId
                    ]);
                }
                $itemId = $this->parts[$seed['item']]->id;
            } else {
                if(!isset($this->inventory[$seed['item']])) {
                    $this->inventory[] = factory(Inventory::class)->create([
                        'dealer_id' => $dealerId
                    ]);
                }
                $itemId = $this->inventory[$seed['item']]->inventory_id;
            }

            // Create Tracking Unit
            $this->units[] = factory(TrackingUnit::class)->create([
                'session_id' => $sessionId,
                'type' => $seed['type'],
                'inventory_id' => $itemId
            ]);
            sleep(1);
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $websiteId = $this->website->getKey();
        $sessionId = $this->tracking->session_id;

        // Database clean up
        TrackingUnit::where('session_id', $sessionId)->delete();
        Tracking::where('session_id', $sessionId)->delete();
        Lead::where('dealer_id', $dealerId)->delete();
        Inventory::where('dealer_id', $dealerId)->delete();
        Part::where('dealer_id', $dealerId)->delete();
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::destroy($websiteId);
        //User::destroy($dealerId);
    }
}
