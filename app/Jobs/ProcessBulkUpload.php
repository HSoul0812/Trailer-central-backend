<?php

namespace App\Jobs;

use App\Models\Bulk\Parts\BulkUpload;
use Illuminate\Support\Facades\Log;

/**
 * 
 *
 * @author Eczek
 */
class ProcessBulkUpload extends Job {
    
    protected $bulk;
    
    protected $csvImportService;
    
    /**
     * Create a new job instance.
     *
     * @return void
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
        try {
            $this->csvImportService->run();
        } catch (\Exception $ex) {
            Log::channel('bulk-upload')->info($ex->getMessage());
        }
    }
    
}
