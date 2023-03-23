<?php

namespace App\Jobs\Bulk\Inventory;

use App\Models\Bulk\Inventory\BulkUpload;
use App\Models\Inventory\Inventory;
use App\Services\Import\Inventory\CsvImportServiceInterface;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Support\Facades\Log;

use App\Jobs\Job;

class ProcessBulkUpload extends Job {

    public $timeout = 0;
    public $tries = 2;

    protected $bulkId;

    /**
     * @var CsvImportServiceInterface
     */
    protected $csvImportService;

    /**
     * Create a new job instance.
     *
     * @param int $bulkId
     */
    public function __construct(int $bulkId)
    {
        $this->bulkId = $bulkId;
    }

    public function handle(CsvImportServiceInterface $importerService, InventoryServiceInterface $inventoryService): void
    {
        Log::info('Starting inventory bulk upload');

        try {
            $bulk = $this->findModel($this->bulkId);

            $importerService->setBulkUpload($bulk);

            Inventory::withoutImageOverlayGenerationSearchSyncingAndCacheInvalidation(static function () use ($importerService): void {
                $importerService->run();
            });

            $inventoryService->invalidateCacheReindexAndGenerateImageOverlaysByDealerIds([$bulk->dealer_id]);

            Log::info(sprintf('Inventory bulk upload %d was processed', $bulk->id));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    protected function findModel(int $int): BulkUpload
    {
        return BulkUpload::find($int);
    }
}
