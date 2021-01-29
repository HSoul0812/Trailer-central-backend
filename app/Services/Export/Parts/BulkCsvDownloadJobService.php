<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Models\Common\MonitoredJob;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface as BulkDownloadRepository;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Class CsvExportService
 *
 * Builds a parts CSV file for export. Typically called by a Job. This is to decouple service code from the job
 */
class BulkCsvDownloadJobService implements BulkDownloadJobServiceInterface
{
    /**
     * @var BulkDownloadRepository
     */
    private $repository;

    /**
     * @var CsvRunnableServiceInterface
     */
    private $runnableService;

    public function __construct(BulkDownloadRepository $bulkRepository, CsvRunnableServiceInterface $runnableService)
    {
        $this->repository = $bulkRepository;
        $this->runnableService = $runnableService;
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
        if ($this->repository->isBusyByDealer($dealerId)) {
            throw new BusyJobException("This job can't be set up due there is currently other job working");
        }

        return $this->repository->create([
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
        $queueableJob = new CsvExportJob($this->runnableService, $job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatch($queueableJob->onQueue($job::QUEUE_NAME));

        $this->repository->update($job->token, ['queue_job_id' => $jobId]);
    }

    /**
     * @param BulkDownload $job
     */
    public function dispatchNow($job): void
    {
        // create a queueable job
        $queueableJob = new CsvExportJob($this->runnableService, $job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatchNow($queueableJob->onQueue($job::QUEUE_NAME));

        $this->repository->update($job->token, ['queue_job_id' => $jobId]);
    }
}
