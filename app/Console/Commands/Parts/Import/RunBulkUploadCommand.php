<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use App\Repositories\Bulk\Parts\BulkUploadRepositoryInterface;
use App\Models\Bulk\Parts\BulkUpload;

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
        // @todo: What's the purpose of this double assignment? So, What's $this->s3Bucket ?
        $bulkId = $this->argument('bulk-id');

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
