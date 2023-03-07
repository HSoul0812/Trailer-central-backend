<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Common\BusyJobException;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Models\Parts\Part;
use App\Models\Parts\Bin;
use App\Repositories\Bulk\Parts\BulkDownloadRepositoryInterface;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Common\AbstractMonitoredJobService;
use App\Services\Export\HasExporterInterface;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Provide capabilities to setup and dispatch a monitored job for parts bulk cvs download, also provide the runner
 * to handle the export of the csv file.
 *
 * This is to decouple service code from the job.
 */
class BulkCsvDownloadJobService extends AbstractMonitoredJobService implements BulkDownloadMonitoredJobServiceInterface,
                                                                               HasExporterInterface
{
    /**
     * @var BulkDownloadRepositoryInterface
     */
    private $bulkRepository;

    /**
     * @var PartRepositoryInterface
     */
    private $partRepository;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(
        BulkDownloadRepositoryInterface $bulkRepository,
        PartRepositoryInterface $partRepository,
        LoggerServiceInterface $logger,
        MonitoredJobRepositoryInterface $monitoredJobsRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->bulkRepository = $bulkRepository;
        $this->partRepository = $partRepository;
        $this->logger = $logger;
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
//            throw new BusyJobException("This job can't be set up due there is currently other job working");
        }

        return $this->bulkRepository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
            'name' => BulkDownload::QUEUE_JOB_NAME
        ]);
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

        // get bin names
        $addedHeaders = $this->partRepository->getBins($job->dealer_id)->map(function(Bin $bin) {
            return $bin->bin_name;
        })->toArray();

        $exporter = $this->getExporter($job);

        // prep the exporter
        $exporter->createFile()
            // set the csv headers
            ->setHeaders(array_merge($exporter->getHeaders(), $addedHeaders, ['Part ID']))

            // a line mapper maps the db columns by name to csv column by position
            ->setLineMapper(static function (\stdClass $part) use ($exporter): array {
                return $exporter->getLineMapper($part);
            })

            // if progress has incremented, save progress
            ->onProgressIncrement(function ($progress) use ($job): bool {
                return $this->bulkRepository->updateProgress($job->token, $progress);
            })

            // set the exporter's source query
            ->setQuery($partsQuery);

        $this->logger->info(sprintf("[%s:] starting to export the file for the monitored job '%s'", __CLASS__, $job->token));

        try {
            $this->bulkRepository->updateProgress($job->token, 0);

            // do the export
            $exporter->export();

            $this->bulkRepository->setCompleted($job->token);
        } catch (Exception $exception) {
            $this->bulkRepository->setFailed($job->token, ['message' => "Got exception: " . $exception->getMessage()]);
            $this->logger->error(sprintf('[%s:] got exception: %s', __CLASS__, $exception->getMessage()));

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
