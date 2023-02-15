<?php

namespace App\Jobs\Inventory;

use Exception;
use App\Jobs\Job;
use Illuminate\Support\Facades\Log;
use App\Services\Inventory\InventoryBulkUpdateManufacturerService;

class InventoryBulkUpdateManufacturer extends Job
{
    //public $timeout = 0;
    public $tries = 2;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var InventoryBulkUpdateManufacturerService
     */
    protected $inventoryBulkUpdateService;

    /**
     * Create a new job instance.
     *
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->inventoryBulkUpdateService = new InventoryBulkUpdateManufacturerService($params);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting Inventory Bulk Manufacturer Update');
        try {
            $this->inventoryBulkUpdateService->update();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
