<?php

namespace App\Jobs\Blog;

use App\Jobs\Job;
use App\Models\Bulk\Blog\BulkPostUpload;
use App\Services\Import\Blog\CsvImportServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 *
 *
 * @author Mert
 */
class ProcessBulkUpload extends Job {

    /**
     * @var BulkPostUpload
     */
    protected $bulk;

    /**
     * @var CsvImportServiceInterface
     */
    protected $csvImportService;

    /**
     * Create a new job instance.
     *
     * @param BulkPostUpload $bulk
     */
    public function __construct(BulkPostUpload $bulk)
    {
        $this->bulk = $bulk;
        $this->csvImportService = app('App\Services\Import\Blog\CsvImportServiceInterface');
        $this->csvImportService->setBulkPostUpload($bulk);
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
