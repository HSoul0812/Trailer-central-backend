<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use App\Models\Inventory\Inventory;

/**
 * Will handle the Scout re-indexation by dealer location
 */
class ReIndexInventoriesByDealerLocationJob extends Job
{
    /**
     * @var array<integer>
     */
    private $locations;

    public $queue = 'scout';

    public function __construct(array $locations)
    {
        $this->locations = $locations;
    }

    public function handle(): void
    {
        Inventory::makeAllSearchableByDealerLocations($this->locations);
    }
}
