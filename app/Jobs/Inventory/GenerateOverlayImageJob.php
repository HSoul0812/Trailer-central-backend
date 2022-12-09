<?php

namespace App\Jobs\Inventory;

use App\Jobs\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Inventory\Inventory;
use App\Services\Inventory\InventoryService;

class GenerateOverlayImageJob extends Job {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * GenerateOverlayImageJob constructor.
     * @param Inventory $inventory
     */
    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * @param InventoryService $service
     * @return void
     */
    public function handle(InventoryService $service)
    {
        try {
            $service->generateOverlays($this->inventory->inventory_id);

            Log::info('Inventory Images with Overlay has been successfully generated', ['inventory_id' => $this->inventory->inventory_id]);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}