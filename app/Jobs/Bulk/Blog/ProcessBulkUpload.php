<?php

namespace App\Jobs\Bulk\Blog;

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
     * @var int
     */
    protected $bulk_id;

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
     * @param int $bulk_id
     */
    public function __construct(int $bulk_id)
    {
        $this->bulk_id = $bulk_id;
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
            $this->bulk = BulkPostUpload::find($this->bulk_id);
            $this->csvImportService = app('App\Services\Import\Blog\CsvImportServiceInterface');
            $this->csvImportService->setBulkPostUpload($this->bulk);
            $this->csvImportService->run();
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
