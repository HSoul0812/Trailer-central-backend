<?php /** @noinspection ALL */

namespace App\Domains\Scout\Traits;

use App\Domains\Scout\Jobs\ExceptionableMakeSearchable;
use Laravel\Scout\ModelObserver;
use Laravel\Scout\Searchable;
use Laravel\Scout\SearchableScope;

trait ExceptionableSearchable
{
    use Searchable;

    public static function bootSearchable()
    {
        // to avoid the original trait behavior which is being override by `bootExceptionableSearchable` ensuring the
        // `registerSearchableMacros` belongs only to those classes which uses this trait
    }

    public static function bootExceptionableSearchable()
    {
        static::addGlobalScope(new SearchableScope);

        static::observe(new ModelObserver);

        (new static)->registerSearchableMacros();
    }

    /**
     * Dispatch the job to make the given models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
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
