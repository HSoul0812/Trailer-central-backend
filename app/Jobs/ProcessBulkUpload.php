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
class ProcessBulkUpload extends Job
{
    public $timeout = 0;

    public $tries = 2;

    /**
     * @var BulkUpload
     */
    protected $bulk;

    /**
     * Create a new job instance.
     *
     * @param BulkUpload $bulk
     */
    public function __construct(BulkUpload $bulk)
    {
        $this->bulk = $bulk;
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
            resolve(CsvImportServiceInterface::class)
                ->setBulkUpload($this->bulk)
                ->run();
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
