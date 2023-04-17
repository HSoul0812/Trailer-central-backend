<?php

namespace App\Services\Common;

use App\Contracts\LoggerServiceInterface;
use App\Models\BatchedJob;
use App\Repositories\Horizon\TagRepositoryInterface;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Carbon;
use Laravel\Horizon\Contracts\JobRepository;
use Illuminate\Support\Str;
use Illuminate\Queue\QueueManager;

class BatchedJobService implements BatchedJobServiceInterface
{
    private const COMPLETED = 'completed';

    /** @var string used when the batch doesn't have a group */
    private const NO_GROUP = 'no-group';

    /** @var int time in seconds */
    public const WAIT_TIME_IN_SECONDS = 2;

    /** @var \Illuminate\Queue\QueueManager */
    private $queueManager;

    /** @var \App\Repositories\Horizon\TagRepositoryInterface */
    private $tagRepository;

    /** @var \Laravel\Horizon\Contracts\JobRepository */
    private $jobRepository;

    /** @var \App\Contracts\LoggerServiceInterface */
    private $logger;

    public function __construct(
        QueueManager $queueManager,
        TagRepositoryInterface $tagRepository,
        JobRepository $jobRepository,
        LoggerServiceInterface $logger
    ) {
        $this->queueManager = $queueManager;
        $this->tagRepository = $tagRepository;
        $this->jobRepository = $jobRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create(
        array $queues,
        ?string $group = null,
        ?int $waitTime = null,
        ?array $context = null
    ): BatchedJob {
        $batch = BatchedJob::create([
            'batch_id' => $this->generateBatchId($group),
            'queues' => $queues,
            'wait_time' => $waitTime ?? self::WAIT_TIME_IN_SECONDS,
            'context' => $context
        ]);

        $this->logger->info(sprintf('Batch [%s] was created', $batch->batch_id));

        $this->tagRepository->monitor($batch->batch_id);

        return $batch;
    }

    /**
     * Generates a unique id like `230417_123042_860-recreate-index-Vm8Kh` where `recreate-index` is the group
     *
     * @param  string|null  $group
     * @return string
     */
    public function generateBatchId(?string $group = null): string
    {
        return sprintf(
            '%s-%s_%s',
            now()->format('ymd_His_v'),
            Str::slug(empty($group) ? self::NO_GROUP : $group),
            Str::random('5')
        );
    }

    /**
     * @inheritDoc
     */
    public function detach(BatchedJob $batch): void
    {
        $processedJobs = $batch->total_jobs - $this->count($batch);

        $this->update($batch, [
            'processed_jobs' => $processedJobs,
            'failed_jobs' => $batch->total_jobs - $processedJobs,
            'finished_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        $this->tagRepository->forget($batch->batch_id);

        if ($processedJobs === $batch->total_jobs) {
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
     * Determines if the batch is still running based on jobs numbers, when it is zero,
     * that means it was fully processed
     *
     * @param  BatchedJob  $batch
     * @return bool
     */
    protected function isRunning(BatchedJob $batch): bool
    {
        if (!empty($batch->queues)) {
            /** @var RedisQueue $connection */
            $queueConnection = $this->queueManager->connection('redis');

            $totalJobsEnqueued = 0;

            foreach ($batch->queues as $queue) {
                $totalJobsEnqueued += $queueConnection->size($queue);
            }

            // given there is a bug in horizon which is not updating properly the monitored jobs,
            // so we have to assume that a batched job is finished when the queue is empty
            if ($totalJobsEnqueued === 0) {
                $this->update($batch, ['processed_jobs' => $batch->total_jobs]);

                $this->logger->info(
                    sprintf('the batch [%s] was finished due related-queues are empty', $batch->batch_id)
                );

                return false;
            }
        }

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
