<?php


namespace App\Services\Export\Parts;


use App\Models\Bulk\Parts\BulkDownload;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface as BulkDownloadRepository;
use App\Repositories\Parts\BinRepository;
use App\Repositories\Parts\PartRepository;
use App\Services\Export\AbstractCsvQueryExporter as CsvQueryExporter;

/**
 * Class CsvExportService
 *
 * Builds a parts CSV file for export. Typically called by a Job. This is to decouple service code from the job
 *
 * @package App\Services\Export\Parts
 */
class CsvExportService implements CsvExportServiceInterface
{
    /**
     * @var BulkDownloadRepository
     */
    private $repository;
    /**
     * @var PartRepository
     */
    private $partRepository;
    /**
     * @var BinRepository
     */
    private $binRepository;

    public function __construct(PartRepository $partRepository, BinRepository $binRepository)
    {
        $this->partRepository = $partRepository;
        $this->binRepository = $binRepository;
    }

    /**
     * Run the service
     * @inheritDoc
     * @throws \Exception
     */
    public function run(BulkDownload $download, CsvQueryExporter $exporter)
    {
        // get stream of parts rows from db
        $partsQuery = $this->partRepository->queryAllByDealerId($download->dealer_id);

        // prep the exporter
        $exporter->createFile()
            // set the csv headers
            ->setHeaders(array_keys($download->lineMapper()))

            // a line mapper maps the db columns by name to csv column by position
            ->setLineMapper(function ($line) use ($download) {
                return $download->lineMapper($line);
            })

            // if progress has incremented, save progress
            ->onProgressIncrement(function($progress) use ($download) {
                $download->progress = $progress;
                return $download->save();
            })

            // set the exporter's source query
            ->setQuery($partsQuery);

        try {//
            $download->status = BulkDownload::STATUS_PROCESSING;
            $download->save();

            // do the export
            $exporter->export();

            // set completed
            $download->status = BulkDownload::STATUS_COMPLETED;
            $download->save();

        } catch (\Exception $e) {
            $download->status = BulkDownload::STATUS_ERROR;
            $download->result = "Got exception: " . $e->getMessage();
            $download->save();

            throw $e;
        }
    }
}
