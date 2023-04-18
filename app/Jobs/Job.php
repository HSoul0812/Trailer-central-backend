<?php

namespace App\Jobs;

use App\Models\BatchedJob;
use App\Services\Common\BatchedJobServiceInterface;
use App\Traits\Horizon\WithTags;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class Job implements ShouldQueue
{
    /** @var string|null */
    private static $batchId;

    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "queueOn" and "delay" queue helper methods.
    |
    */

    use InteractsWithQueue, Queueable, SerializesModels, WithTags;

    /**
     * Will create the batch, then it will run the anonymous function and monitor the batch, it will return the batch
     * when it is finished or when the related-queues are empty
     *
     * @param  callable  $callback
     * @param  string[]  $queues list of monitored queues
     * @param  string|null  $group
     * @param  int|null  $waitTime  time in seconds to wait for monitored job to be checked if it was finished
     * @param  array|null  $context
     * @return BatchedJob
     */
    public static function batch(
        callable $callback,
        array $queues,
        ?string $group = null,
        ?int $waitTime = null,
        ?array $context = null
    ): BatchedJob
    {
        /** @var BatchedJobServiceInterface $service */
        $service = app(BatchedJobServiceInterface::class);

        $batch = $service->create($queues, $group, $waitTime, $context);

        self::$batchId = $batch->batch_id;

        try {
            $callback($batch);

            $service->monitor($batch);

            return $batch;
        } finally {
            $service->detach($batch);

            self::$batchId = null;
        }
    }

    /**
     * Gets the current batch id when it exist
     *
     * @return string|null
     */
    public static function batchId(): ?string
    {
        return self::$batchId;
    }
}
