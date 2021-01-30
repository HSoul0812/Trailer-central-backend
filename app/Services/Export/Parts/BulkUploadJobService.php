<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Jobs\ProcessBulkUpload;
use App\Models\Bulk\Parts\BulkUpload;
use App\Models\Bulk\Parts\BulkUploadPayload;
use App\Repositories\Bulk\BulkUploadRepositoryInterface;
use Illuminate\Contracts\Bus\Dispatcher;

class BulkUploadJobService implements BulkUploadMonitoredJobServiceInterface
{
    /**
     * @var BulkUploadRepositoryInterface
     */
    private $repository;

    public function __construct(BulkUploadRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $dealerId
     * @param array|BulkUploadPayload $payload
     * @param string|null $token
     * @return BulkUpload
     */
    public function setup(int $dealerId, $payload, ?string $token = null): BulkUpload
    {
        return $this->repository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => BulkUpload::QUEUE_NAME,
            'concurrency_level' => BulkUpload::LEVEL_DEFAULT,
            'name' => BulkUpload::QUEUE_JOB_NAME
        ]);
    }

    /**
     * @param BulkUpload $job
     */
    public function dispatchNow($job): void
    {
        // create a queueable job
        $queueableJob = new ProcessBulkUpload($job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatchNow($queueableJob->onQueue($job::QUEUE_NAME));

        $this->repository->update($job->token, ['queue_job_id' => $jobId]);
    }

    /**
     * @param BulkUpload $job
     */
    public function dispatch($job): void
    {
        // create a queueable job
        $queueableJob = new ProcessBulkUpload($job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatch($queueableJob->onQueue($job::QUEUE_NAME));

        $this->repository->update($job->token, ['queue_job_id' => $jobId]);
    }
}
