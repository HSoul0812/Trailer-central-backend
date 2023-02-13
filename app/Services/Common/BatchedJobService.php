<?php

namespace App\Services\Common;

use App\Contracts\LoggerServiceInterface;
use App\Models\BatchedJob;
use App\Repositories\Horizon\TagRepositoryInterface;
use Illuminate\Support\Carbon;
use Laravel\Horizon\Contracts\JobRepository;
use Illuminate\Support\Str;

class BatchedJobService implements BatchedJobServiceInterface
{
    private const COMPLETED = 'completed';

    /** @var string used when the batch doesn't have a group */
    private const NO_GROUP = 'no-group';

    /** @var int time in seconds */
    public const WAIT_TIME = 2;

    /** @var \App\Repositories\Horizon\TagRepositoryInterface */
    private $tagRepository;

    /** @var \Laravel\Horizon\Contracts\JobRepository */
    private $jobRepository;

    /** @var \App\Contracts\LoggerServiceInterface */
    private $logger;

    public function __construct(
        TagRepositoryInterface $tagRepository,
        JobRepository $jobRepository,
        LoggerServiceInterface $logger
    ) {
        $this->tagRepository = $tagRepository;
        $this->jobRepository = $jobRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create(?string $group = null, ?int $waitTime = null, ?array $context = null): BatchedJob
    {
        $batch = BatchedJob::create([
            'batch_id' => Str::uuid()->toString(),
            'group' => $group ?: self::NO_GROUP,
            'wait_time' => $waitTime ?? self::WAIT_TIME,
            'context' => $context
        ]);

        $this->logger->info(sprintf('Batch [%s] was created', $batch->batch_id));

        $this->tagRepository->monitor($batch->batch_id);

        return $batch;
    }

    /**
     * @inheritDoc
     */
    public function detach(BatchedJob $batch): void
    {
        $processed_jobs = $batch->total_jobs - $this->count($batch);

        $this->update($batch, [
            'processed_jobs' => $processed_jobs,
            'failed_jobs' => $batch->total_jobs - $processed_jobs,
            'finished_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        $this->tagRepository->forget($batch->batch_id);

        if ($processed_jobs === $batch->total_jobs) {
            $this->tagRepository->stopMonitoring($batch->batch_id);
        }

        $this->logger->info(sprintf('Batch [%s] was forgot', $batch->batch_id));
    }

    /**
     * @inheritDoc
     */
    public function monitor(BatchedJob $batch): void
    {
        $this->update($batch, ['total_jobs' => $this->count($batch)]);

        $this->logger->info(sprintf('Batch [%s] was started to be monitored', $batch->batch_id));

        do {
            $this->waitFor($batch);
        } while ($this->isRunning($batch));
    }

    /**
     * @inheritDoc
     */
    public function count(BatchedJob $batch): int
    {
        return $this->tagRepository->count($batch->batch_id);
    }

    /**
     * Determines if the batch is still running based on jobs numbers, when it is zero, that means it was fully processed
     *
     * @param  BatchedJob  $batch
     * @return bool
     */
    protected function isRunning(BatchedJob $batch): bool
    {
        $jobIds = $this->tagRepository->jobs($batch->batch_id);

        $this->jobRepository->getJobs($jobIds)->each(function (\stdClass $job) use ($batch, &$jobIds) {
            if ($job->status === self::COMPLETED) {
                $this->tagRepository->detach($batch->batch_id, $job->id);
                unset($jobIds[$job->id]);

                $this->update($batch, ['processed_jobs' => $batch->total_jobs - count($jobIds)]);

                $this->logger->info(sprintf('[%s] was detached from the batch [%s]', $job->id, $batch->batch_id));
            }
        });

        return count($jobIds);
    }

    /**
     * Updates a given batch by using given properties
     *
     * @param  BatchedJob  $job
     * @param  array  $properties
     * @return BatchedJob
     */
    protected function update(BatchedJob $job, array $properties): BatchedJob
    {
        $job->fill($properties)->save();

        return $job;
    }

    protected function waitFor(BatchedJob $batch): void
    {
        sleep($batch->wait_time);
    }
}
