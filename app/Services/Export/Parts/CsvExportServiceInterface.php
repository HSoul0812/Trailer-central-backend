<?php


namespace App\Services\Export\Parts;


use App\Models\Bulk\Parts\BulkDownload;
use App\Services\Export\AbstractCsvQueryExporter as CsvQueryExporterAlias;

interface CsvExportServiceInterface
{
    /**
     * Run the export service based on the download instance
     * @param BulkDownload $download
     * @param CsvQueryExporterAlias $exporter
     * @return mixed
     */
    public function run(BulkDownload $download, CsvQueryExporterAlias $exporter);
}
