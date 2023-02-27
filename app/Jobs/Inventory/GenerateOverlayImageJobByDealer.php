<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\InventoryServiceInterface;

class GenerateOverlayImageJobByDealer extends Job
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * @var int
     */
    public $dealerId;

    public $queue = 'overlay-images';

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
            ], false, false, [Inventory::getTableName().'.inventory_id']
        );

        if ($inventories->count() > 0) {
            Job::batch(static function (BatchedJob $job) use ($inventories) {
                foreach ($inventories as $inventory) {
                    dispatch(new GenerateOverlayImageJob($inventory->inventory_id,false))->onQueue('overlay-images');
                }
            },__CLASS__, 5);

            // we can not inject `InventoryServiceInterface` into constructor to avoid cyclic dependency
            $service->invalidateCacheAndReindexByDealerIds([$this->dealerId]);
        }
    }
}
