<?php

namespace App\Jobs;

use App\Models\Bulk\Parts\BulkUpload;
use App\Services\Import\Parts\CsvImportServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 *
 *
 * @author Eczek
 */
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
        $this->csvImportService = app('App\Services\Import\Parts\CsvImportServiceInterface');
        $this->csvImportService->setBulkUpload($bulk);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting bulk upload');
        try {
            $this->csvImportService->run();
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
