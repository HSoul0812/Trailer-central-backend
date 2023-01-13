<?php

namespace App\Jobs\Website;

use App\Jobs\Job;
use App\Models\Inventory\Inventory;

class ReIndexInventoriesByDealersJob extends Job
{
    /**
     * @var array<integer>
     */
    private $dealers;

    public $queue = 'scout';

    public function __construct(array $dealers)
    {
        $this->dealers = $dealers;
    }

    public function handle(): void
    {
        Inventory::makeAllSearchableByDealers($this->dealers);
    }
}
