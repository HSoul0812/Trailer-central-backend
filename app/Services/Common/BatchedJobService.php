<?php

namespace App\Services\Common;

use App\Models\BatchedJob;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Support\Carbon;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\TagRepository;
use Str;

class BatchedJobService implements BatchedJobServiceInterface
{
    private const COMPLETED = 'completed';

    private const WAIT_TIME = 2;

    private const NO_GROUP = 'no-group';

    /** @var \Laravel\Horizon\Contracts\TagRepository */
    private $tagRepository;

    /** @var \Laravel\Horizon\Contracts\JobRepository */
    private $jobRepository;

    /** @var \Illuminate\Contracts\Redis\Factory */
    private $redis;

    public function __construct(TagRepository $tagRepository, JobRepository $jobRepository, RedisFactory $redis)
    {
        $this->tagRepository = $tagRepository;
        $this->jobRepository = $jobRepository;
        $this->redis = $redis;
    }

    /**
     * @inheritDoc
     */
    public function create(?string $group = null): BatchedJob
    {
        $batch = BatchedJob::create([
            'batch_id' => Str::uuid()->toString(),
            'group' => $group ?: self::NO_GROUP
        ]);

        $this->tagRepository->monitor($batch->batch_id);

        return $batch;
    }

    /**
     * @inheritDoc
     */
    public function stop(BatchedJob $batch): void
    {
        $processed_jobs = $batch->total_jobs - $this->count($batch);

        $this->update($batch, [
            'processed_jobs' => $processed_jobs,
            'failed_jobs' => $batch->total_jobs - $processed_jobs,
            'finished_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        $this->tagRepository->stopMonitoring($batch->batch_id);
    }

    /**
     * @inheritDoc
     */
    public function monitor(BatchedJob $batch): void
    {
        $this->update($batch, ['total_jobs' => $this->count($batch)]);

        do {
            $this->wait();
        } while ($this->isRunning($batch));

        $this->forget($batch);
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
                $this->connection()->zrem($batch->batch_id, $job->id);
                unset($jobIds[$job->id]);

                $this->update($batch, ['processed_jobs' => $batch->total_jobs - count($jobIds)]);
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

    /**
     * Forget a batch given a batch
     *
     * @param  BatchedJob  $batch
     * @return void
     */
    protected function forget(BatchedJob $batch): void
    {
        $this->tagRepository->forget($batch->batch_id);
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    protected function connection()
    {
        return $this->redis->connection('horizon');
    }

    protected function wait(): void
    {
        sleep(self::WAIT_TIME);
    }
}
