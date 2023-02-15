<?php

namespace App\Jobs\Inventory;

use Exception;
use App\Jobs\Job;
use Illuminate\Support\Facades\Log;
use App\Services\Inventory\InventoryBulkUpdateManufacturerServiceInterface;

class InventoryBulkUpdateManufacturer extends Job
{
    //public $timeout = 0;
    public $tries = 2;

    /**
     * @var array
     */
    protected $params;

    /**
     * Create a new job instance.
     *
     * @param array $params
     */
    public function __construct(
        array $params
    )
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InventoryBulkUpdateManufacturerServiceInterface $inventoryBulkUpdateService)
    {
        Log::info('Starting Inventory Bulk Manufacturer Update');
        try {
            $inventoryBulkUpdateService->update($this->params);
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
