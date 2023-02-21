<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Services\Inventory\InventoryServiceInterface;

class GenerateOverlayImageJobByDealer extends Job {

    use Dispatchable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * @var int
     */
    private $dealerId;

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
            ], false, false, [Inventory::getTableName(). '.inventory_id']
        );

        if ($inventories->count() > 0) {
            Job::batch(function (BatchedJob $job) use($inventories){
                foreach ($inventories as $inventory) {
                    $this->dispatch((new GenerateOverlayImageJob($inventory->inventory_id))->onQueue('overlay-images'));
                }
            });

            // we can not inject `InventoryServiceInterface` into constructor to avoid cyclic dependency
            $service->invalidateCacheAndReindexByDealerIds([$this->dealerId]);
        }
    }
}
