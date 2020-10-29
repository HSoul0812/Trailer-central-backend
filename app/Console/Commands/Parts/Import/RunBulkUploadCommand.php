<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use App\Repositories\Bulk\BulkUploadRepositoryInterface;
use App\Models\Bulk\Parts\BulkUpload;
use App\Models\Parts\Part;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

/**
 * Class SyncPartsCommand
 */
class RunBulkUploadCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "run:bulk {bulk-id?}";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(BulkUploadRepositoryInterface $bulkUploadRepo)
    { 
        $bulkId = $this->s3Bucket = $this->argument('bulk-id');
        
        if ($bulkId) {
            $bulk = $bulkUploadRepo->get(['id' => $bulkId]);
        } else {
            $bulk = $bulkUploadRepo->get(['status' => BulkUpload::PROCESSING]);
        }
        
        if (empty($bulk)) {
            return;
        }        
        $service = app('App\Services\Import\Parts\CsvImportServiceInterface');
        $service->setBulkUpload($bulk);
        $service->run();
    }
}
