<?php


namespace App\Jobs\Bulk\Parts;


use App\Jobs\Job;
use App\Models\Bulk\Parts\BulkDownload;
use App\Services\Export\AbstractCsvQueryExporter;
use App\Services\Export\Parts\CsvExportService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use League\Csv\Exception;

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
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var CsvExportService
     */
    private $service;
    /**
     * @var AbstractCsvQueryExporter
     */
    private $exporter;

    public function __construct(CsvExportService $service, BulkDownload $download, AbstractCsvQueryExporter $exporter)
    {
        $this->download = $download;
        $this->service = $service;
        $this->exporter = $exporter;
    }

    public function handle()
    {
        try {
            $this->service->run($this->download, $this->exporter);
            return true;

        } catch (Exception $e) {
            // catch and log
            Log::error("Error running export parts CSV export job: ".
                "id[{$this->download->id}] ".
                "token[{$this->download->token}] exception[{$e->getMessage()}]"
            );

            throw $e;
        }
    }
}
