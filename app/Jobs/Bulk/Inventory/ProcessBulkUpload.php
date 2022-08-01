<?php

namespace App\Jobs\Bulk\Inventory;

use App\Models\Bulk\Inventory\BulkUpload;
use App\Services\Import\Inventory\CsvImportServiceInterface;
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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CsvImportServiceInterface $service)
    {
        Log::info('Starting inventory bulk upload');
        try {
            $bulk = BulkUpload::find($this->bulkId);
            $service->setBulkUpload($bulk);

            $service->run();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
