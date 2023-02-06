<?php

namespace App\Domains\Scout\Traits;

use App\Domains\Scout\Jobs\ExceptionableMakeSearchable;
use App\Indexers\Searchable;

trait ExceptionableSearchable
{
    use Searchable;

    /**
     * Dispatch the job to make the given models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return mixed
     */
    public function queueMakeSearchable($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        if (!config('scout.queue')) {
            return $models->first()->searchableUsing()->update($models);
        }

        dispatch((new ExceptionableMakeSearchable($models))
            ->onQueue($models->first()->syncWithSearchUsingQueue())
            ->onConnection($models->first()->syncWithSearchUsing()));
    }
}
