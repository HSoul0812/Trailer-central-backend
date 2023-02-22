<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Services\Inventory\InventoryServiceInterface;

class GenerateOverlayImageJob extends Job {

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
    private $inventoryId;

    const MISSING_INVENTORY_ERROR_MESSAGE = 'No query results for model [App\Models\Inventory\Inventory].';

    /**
     * @var bool
     */
    private $reindexAndInvalidateCache;

    /**
     * GenerateOverlayImageJob constructor.
     * @param int $inventoryId
     */
    public function __construct(int $inventoryId, bool $reindexAndInvalidateCache = true)
    {
        $this->inventoryId = $inventoryId;
        $this->reindexAndInvalidateCache = $reindexAndInvalidateCache;
    }

    /**
     * @param  InventoryServiceInterface  $service
     * @param  InventoryRepositoryInterface  $repo
     * @return void
     */
    public function handle(InventoryServiceInterface $service, InventoryRepositoryInterface $repo)
    {
        // Initialize Log File
        $log = Log::channel('inventory-overlays');

        // Try Generating Overlays
        try {
            $itHasGeneratedOverlay = Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($service) {
                return $service->generateOverlays($this->inventoryId);
            });

            if ($this->reindexAndInvalidateCache && $itHasGeneratedOverlay) {
                /** @var Inventory $inventory */
                $inventory = $repo->get(['inventory_id' => $this->inventoryId]);

                $log->info('it will dispatch jobs for sync to index and invalidate cache', [
                    'inventory_id' => $inventory->inventory_id, 'dealer_id' => $inventory->dealer_id
                ]);

                $service->invalidateCacheAndReindexByDealerIds([$inventory->dealer_id]);
            }

            $log->info('Inventory Images with Overlay has been successfully generated', ['inventory_id' => $this->inventoryId]);
        } catch (\Exception $e) {

            $errorMessage = $e->getMessage();

            $log->error($errorMessage);
            $log->error($e->getTraceAsString());

            // assuming there's a race condition when transaction commit is taking longer to process when adding new inventory
            if ($errorMessage === self::MISSING_INVENTORY_ERROR_MESSAGE) {

                // put back to queue after one second
                $this->release(1);
            }
        }
    }
}
