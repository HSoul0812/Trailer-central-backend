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
     * This method is a hotfix to avoid to use last model which register the searchable macro
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
