<?php

namespace App\Jobs\Bulk\Inventory;

use App\Models\Bulk\Inventory\BulkUpload;
use App\Services\Import\Inventory\CsvImportServiceInterface;
use Illuminate\Support\Facades\Log;

use App\Jobs\Job;

class ProcessBulkUpload extends Job {

    public $timeout = 0;
    public $tries = 2;

    /**
     * @var BulkUpload
     */
    protected $bulk;

    /**
     * @var CsvImportServiceInterface
     */
    protected $csvImportService;

    /**
     * Create a new job instance.
     *
     * @param BulkUpload $bulk
     */
    public function __construct(BulkUpload $bulk)
    {
        $this->bulk = $bulk;
        $this->csvImportService = app('App\Services\Import\Inventory\CsvImportServiceInterface');
        $this->csvImportService->setBulkUpload($bulk);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting inventory bulk upload');
        try {
            $this->csvImportService->run();
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
