<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryServiceInterface;

class GenerateOverlayImageJob extends Job {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * GenerateOverlayImageJob constructor.
     * @param int $inventoryId
     */
    public function __construct(int $inventoryId)
    {
        $this->inventoryId = $inventoryId;
    }

    /**
     * @param InventoryServiceInterface $service
     * @return void
     */
    public function handle(InventoryServiceInterface $service)
    {
        // Initialize Log File
        $log = Log::channel('inventory-overlays');

        // Try Generating Overlays
        try {
            Inventory::withoutCacheInvalidationAndSearchSyncing(function () use($service){
                $service->generateOverlays($this->inventoryId);
            });

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
