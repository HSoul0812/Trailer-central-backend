<?php

namespace App\Jobs\Bulk\Parts;

use App\Jobs\Job;
use App\Models\Bulk\Parts\BulkDownload;
use App\Services\Common\RunnableJobServiceInterface;
use App\Services\Export\Parts\BulkDownloadMonitoredJobServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class CsvExportJob
 *
 * Job wrapper for CsvExporterService
 *
 * @package App\Services\Export\Parts
 */
class CsvExportJob extends Job
{
    /**
     * @var BulkDownload
     */
    private $download;

    /**
     * @var RunnableJobServiceInterface
     */
    private $service;

    public function __construct(BulkDownload $download)
    {
        $this->service = app(BulkDownloadMonitoredJobServiceInterface::class);
        $this->download = $download;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function handle(): bool
    {
        try {
            $this->service->run($this->download);
        } catch (Exception $e) {
            // catch and log

            $payload = implode(',',$this->download->payload->asArray());

            Log::error("Error running export parts CSV export job: ".
                "token[{$this->download->token}, payload={{$payload}}] exception[{$e->getMessage()}]"
            );

            throw $e;
        }

        return true;
    }
}
