<?php

namespace App\Services\Common;

use App\Models\BatchedJob;

interface BatchedJobServiceInterface
{
    /**
     * Creates a batch, then start to monitoring it
     *
     * @param string[] $queues
     * @param  string|null  $group
     * @param  int|null  $waitTime  time in seconds
     * @param  array|null  $context
     * @return BatchedJob
     */
    public function create(
        array $queues,
        ?string $group = null,
        ?int $waitTime = null,
        ?array $context = null
    ): BatchedJob;

    /**
     * Generates a unique id like `recreate-index-1681739822-Vm8Kh` where `recreate-index` is the group
     *
     * @param  string|null  $group
     * @return string
     */
    public function generateBatchId(?string $group = null): string;

    /**
     * Detaches a monitored batch
     *
     * @param  BatchedJob  $batch
     * @return void
     */
    public function detach(BatchedJob $batch): void;

    /**
     * Count number of jobs for a given batch
     *
     * @param  BatchedJob  $batch
     * @return int
     */
    public function count(BatchedJob $batch): int;

    /**
     * Monitors a given batch, it will lock the current process until it is finished, or some another
     * rule provokes an batch interruption
     *
     * @param  BatchedJob  $batch
     * @return void
     */
    public function monitor(BatchedJob $batch): void;
}
