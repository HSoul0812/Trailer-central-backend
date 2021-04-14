<?php

declare(strict_types=1);

namespace App\Transformers\Jobs;

use App\Models\Common\MonitoredJob;
use League\Fractal\TransformerAbstract;

class MonitoredJobsTransformer extends TransformerAbstract
{
    public function transform(MonitoredJob $job): array
    {
        return [
            'token' =>  $job->token,
            'name' =>  $job->name,
            'status' => $job->status,
            'progress' => $job->progress,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
            'finished_at' => $job->finished_at
        ];
    }
}
