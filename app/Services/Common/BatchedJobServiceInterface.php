<?php

namespace App\Services\Common;

use App\Models\BatchedJob;

interface BatchedJobServiceInterface
{
    /**
     * Creates a batch, then start to monitoring it
     *
     * @param  string|null  $group
     * @param  int|null  $waitTime time in seconds
     * @return BatchedJob
     */
    public function create(?string $group = null, ?int $waitTime = null): BatchedJob;

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
