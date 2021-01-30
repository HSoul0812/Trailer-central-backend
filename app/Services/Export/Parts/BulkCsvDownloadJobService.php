<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Models\Common\MonitoredJob;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Common\RunnableJobServiceInterface;
use App\Services\Export\HasExporterInterface;
use Exception;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Storage;

/**
 * Class CsvExportService
 *
 * Builds a parts CSV file for export. Typically called by a Job. This is to decouple service code from the job
 */
class BulkCsvDownloadJobService implements BulkDownloadMonitoredJobServiceInterface, RunnableJobServiceInterface, HasExporterInterface
{
    /**
     * @var BulkDownloadRepositoryInterface
     */
    private $bulkRepository;

    /**
     * @var PartRepositoryInterface
     */
    private $partRepository;

    public function __construct(BulkDownloadRepositoryInterface $bulkRepository,PartRepositoryInterface $partRepository)
    {
        $this->bulkRepository = $bulkRepository;
        $this->partRepository = $partRepository;
    }

    /**
     * @param int $dealerId
     * @param array|BulkDownloadPayload $payload
     * @param string|null $token
     * @return BulkDownload
     * @throws BusyJobException when there is currently other job working
     */
    public function setup(int $dealerId, $payload, ?string $token = null): BulkDownload
    {
        if ($this->bulkRepository->isBusyByDealer($dealerId)) {
            throw new BusyJobException("This job can't be set up due there is currently other job working");
        }

        return $this->bulkRepository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => MonitoredJob::LEVEL_BY_DEALER,
            'name' => BulkDownload::QUEUE_JOB_NAME
        ]);
    }

    /**
     * @param BulkDownload $job
     */
    public function dispatch($job): void
    {
        // create a queueable job
        $queueableJob = new CsvExportJob($this, $job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatch($queueableJob->onQueue($job::QUEUE_NAME));

        $this->bulkRepository->update($job->token, ['queue_job_id' => $jobId]);
    }

    /**
     * @param BulkDownload $job
     */
    public function dispatchNow($job): void
    {
        // create a queueable job
        $queueableJob = new CsvExportJob($this, $job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatchNow($queueableJob->onQueue($job::QUEUE_NAME));

        $this->bulkRepository->update($job->token, ['queue_job_id' => $jobId]);
    }

    /**
     * Run the service
     *
     * @param BulkDownload $job
     * @return mixed|void
     * @throws Exception
     */
    public function run($job)
    {
        // get stream of parts rows from db
        $partsQuery = $this->partRepository->queryAllByDealerId($job->dealer_id);

        $exporter = $this->getExporter($job);
        // prep the exporter
        $exporter->createFile()
            // set the csv headers
            ->setHeaders($exporter->getHeaders())

            // a line mapper maps the db columns by name to csv column by position
            ->setLineMapper(static function ($line) use ($job, $exporter) {
                return $exporter->getLineMapper($line);
            })

            // if progress has incremented, save progress
            ->onProgressIncrement(function ($progress) use ($job): bool {
                return $this->bulkRepository->updateProgress($job->token, $progress);
            })

            // set the exporter's source query
            ->setQuery($partsQuery);

        try {
            $this->bulkRepository->updateProgress($job->token, 0);

            // do the export
            $exporter->export();

            $this->bulkRepository->setCompleted($job->token);
        } catch (Exception $exception) {
            $this->bulkRepository->setFailed($job->token, ['message' => "Got exception: " . $exception->getMessage()]);

            throw $exception;
        }
    }

    /**
     * @param BulkDownload $job
     * @return FilesystemCsvExporter
     */
    public function getExporter($job): FilesystemCsvExporter
    {
        return new FilesystemCsvExporter(Storage::disk('partsCsvExports'), $job->payload->export_file);
    }
}
