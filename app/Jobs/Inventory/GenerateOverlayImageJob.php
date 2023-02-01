<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Inventory\InventoryService;

class GenerateOverlayImageJob extends Job {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $inventoryId;

    /**
     * GenerateOverlayImageJob constructor.
     * @param int $inventoryId
     */
    public function __construct(int $inventoryId)
    {
        $this->inventoryId = $inventoryId;
    }

    /**
     * @param InventoryService $service
     * @return void
     */
    public function handle(InventoryService $service)
    {
        // Initialize Log File
        $log = Log::channel('inventory-overlays');

        // Try Generating Overlays
        try {
            $service->generateOverlays($this->inventoryId);

            $log->info('Inventory Images with Overlay has been successfully generated', ['inventory_id' => $this->inventoryId]);
        } catch (\Exception $e) {
            $log->error($e->getMessage());
            $log->error($e->getTraceAsString());
        }
    }
}