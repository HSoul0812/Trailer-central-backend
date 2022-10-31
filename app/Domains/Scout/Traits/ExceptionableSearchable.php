<?php /** @noinspection ALL */

namespace App\Domains\Scout\Traits;

use App\Domains\Scout\Jobs\ExceptionableMakeSearchable;
use Laravel\Scout\Searchable;

trait ExceptionableSearchable
{
    use Searchable;

    /**
     * Dispatch the job to make the given models searchable.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return void
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
