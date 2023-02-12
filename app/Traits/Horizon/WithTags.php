<?php

namespace App\Traits\Horizon;

use App\Jobs\Job;
use Laravel\Horizon\Tags;

/**
 * This trait is intended to be used in jobs, the job which use this trait will have the capability to tag any model
 * within it and will be able to tag by the current batch it if it exists.
 */
trait WithTags
{
    public function tags(): array
    {
        $tags = Tags::modelsFor(Tags::targetsFor($this))->map(function ($model): string {
            return get_class($model).':'.$model->getKey();
        })->all();

        if (Job::batchId()) {
            $tags[] = Job::batchId();
        }

        return $tags;
    }
}
