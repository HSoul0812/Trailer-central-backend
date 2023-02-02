<?php

namespace App\Jobs\Website;

use App\Jobs\Job;
use App\Models\Inventory\Inventory;

class ReIndexInventoriesByDealersJob extends Job
{
    /**
     * @var array<integer>
     */
    private $dealerIds;

    public $queue = 'scout';

    public function __construct(array $dealerIds)
    {
        $this->dealerIds = $dealerIds;
    }

    public function handle(): void
    {
        Inventory::makeAllSearchableByDealers($this->dealerIds);
    }
}
