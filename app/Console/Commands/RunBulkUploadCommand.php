<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Bulk\Parts\BulkUploadRepository;
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
    protected $signature = "run:bulk";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bulkUploadRepo = new BulkUploadRepository;
        $bulk = $bulkUploadRepo->get(['status' => BulkUpload::PROCESSING]);
        if (empty($bulk)) {
            return;
        }
        $service = app('App\Services\Import\Parts\CsvImportServiceInterface');
        $service->setBulkUpload($bulk);
        $service->run();
    }
}
