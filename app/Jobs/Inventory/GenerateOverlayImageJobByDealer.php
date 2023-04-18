<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\InventoryServiceInterface;

class GenerateOverlayImageJobByDealer extends Job
{
    /** @var int time in seconds */
    private const WAIT_TIME_IN_SECONDS = 2;

    /** @var string[] list of queues which are monitored */
    private const MONITORED_QUEUES = ['overlay-images'];

    /** @var string  */
    private const MONITORED_GROUP = 'inventory-generate-overlays-by-dealer';

    /** @var int The number of times the job may be attempted. */
    public $tries = 1;

    /** @var int */
    public $dealerId;

    /** @var string */
    public $queue = 'batched-jobs';

    public function __construct(int $dealerId)
    {
        $this->dealerId = $dealerId;
    }

    public function handle(InventoryRepositoryInterface $repo, InventoryServiceInterface $service)
    {
        $inventories = $repo->getAll(
            [
                'dealer_id' => $this->dealerId,
                'images_greater_than' => 1
            ],
            false,
            false,
            [Inventory::getTableName().'.inventory_id']
        );

        if ($inventories->count() > 0) {
            Job::batch(
                static function (BatchedJob $job) use ($inventories) {
                    foreach ($inventories as $inventory) {
                        dispatch(new GenerateOverlayImageJob($inventory->inventory_id, false));
                    }
                },
                self::MONITORED_QUEUES,
                self::MONITORED_GROUP.'-'.$this->dealerId,
                self::WAIT_TIME_IN_SECONDS
            );

            // we can not inject `InventoryServiceInterface` into constructor to avoid cyclic dependency
            $service->invalidateCacheAndReindexByDealerIds([$this->dealerId]);
        }
    }
}
