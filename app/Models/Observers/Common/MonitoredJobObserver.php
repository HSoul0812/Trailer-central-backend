<?php

declare(strict_types=1);

namespace App\Models\Observers\Common;

use App\Models\Common\MonitoredJob;
use Carbon\Carbon;
use Exception;
use Ramsey\Uuid\Uuid;

/**
 * Implementation of default events for saving on MonitoredJob
 */
class MonitoredJobObserver
{
    /**
     * @param MonitoredJob $model
     * @throws Exception
     */
    public function creating(MonitoredJob $model): void
    {
        // If there is not a provided value for token, it will be generate by default
        $model->token = $model->token ?: Uuid::uuid4()->toString();

        $model->queue = $model->queue ?: MonitoredJob::QUEUE_NAME;
        $model->name = $model->name ?: MonitoredJob::QUEUE_JOB_NAME;
    }

    public function updating(MonitoredJob $model): void
    {
        $model->updated_at = Carbon::now()->format('Y-m-d H:i:s');
    }

    /**
     * Event handler for all saving events
     *
     * This method could handle a notification via web socket, it is a good feature to avoid the job monitoring via
     * ajax requests, right now it is not important, but this is the place.
     *
     * @param MonitoredJob $model
     */
    public function saving(MonitoredJob $model): void
    {
        if ($model->progress >= 100) {
            // if the progress is rather than 100 it will marked as completed and its finished_at will be
            // updated with the current time
            $model->status = MonitoredJob::STATUS_COMPLETED;
            $model->finished_at = Carbon::now()->format('Y-m-d H:i:s');
        }

        if ($model->status === MonitoredJob::STATUS_FAILED) {
            $model->finished_at = Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}
