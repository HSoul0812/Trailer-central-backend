<?php

namespace App\Indexers;

use Illuminate\Support\Collection as BaseCollection;
use Laravel\Scout\Searchable as ScoutSearchable;

trait Searchable
{
    use ScoutSearchable;

    /**
     * Register the searchable macros.
     *
     * This method is a hotfix to avoid to use last model which is registered by the searchable macro,
     * it was a source of bugs
     *
     * @return void
     */
    public function registerSearchableMacros(): void
    {
        BaseCollection::macro('searchable', function (): void {
            if ($this->isNotEmpty()) {
                $this->first()->queueMakeSearchable($this);
            }
        });

        BaseCollection::macro('unsearchable', function (): void {
            if ($this->isNotEmpty()) {
                $this->first()->queueRemoveFromSearch($this);
            }
        });
    }
}
