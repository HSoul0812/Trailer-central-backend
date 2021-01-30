<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Exceptions\Common\HasNotQueueableJob;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;

abstract class AbstractMonitoredJobService implements MonitoredJobServiceInterface
{
    /**
     * @var MonitoredJobRepositoryInterface
     */
    protected $repository;

    public function __construct(MonitoredJobRepositoryInterface $bulkRepository)
    {
        $this->repository = $bulkRepository;
    }

    /**
     * @param MonitoredJob $job
     * @throws HasNotQueueableJob when there is not defined a queueable job
     */
    public function dispatch($job): void
    {
        // create a queueable job
        $queueableJob = $this->createQueueableJob($job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatch($queueableJob->onQueue($job::QUEUE_NAME));

        $this->repository->update($job->token, ['queue_job_id' => $jobId]);
    }

    /**
     * @param MonitoredJob $job
     * @throws HasNotQueueableJob when there is not defined a queueable job
     */
    public function dispatchNow($job): void
    {
        $queueableJob = $this->createQueueableJob($job);

        // dispatch job to queue
        $jobId = app(Dispatcher::class)->dispatchNow($queueableJob->onQueue($job::QUEUE_NAME));

        $this->repository->update($job->token, ['queue_job_id' => $jobId]);
    }

    /**
     * Determine if a monitored job can be set up
     *
     * @param string $concurrencyLevel
     * @param int $dealerId
     * @param string $jobName
     * @return bool
     */
    protected function isAvailable(string $concurrencyLevel, int $dealerId, string $jobName): bool
    {
        switch ($concurrencyLevel) {
            case MonitoredJob::LEVEL_BY_DEALER:
                return $this->repository->isBusyByDealer($dealerId);
            case MonitoredJob::LEVEL_BY_JOB:
                return $this->repository->isBusyByJobName($jobName);
        }

        return true;
    }

    /**
     * @param MonitoredJob $job
     * @return Queueable
     * @throws HasNotQueueableJob
     */
    protected function createQueueableJob($job)
    {
        if ($job->hasQueueableJob()) {
            return $job->getQueueableJob()($job);
        }

        throw new HasNotQueueableJob("This job can't be dispatched due there is not defined a queueable job");
    }

    /**
     * @param MonitoredJob $job
     * @return MonitoredJobRepositoryInterface
     */
    protected function repositoryFrom($job)
    {
        return app($job::REPOSITORY_INTERFACE_NAME);
    }
}
