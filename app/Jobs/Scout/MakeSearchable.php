<?php

namespace App\Jobs\Scout;

use App\Traits\Horizon\WithTags;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;

class MakeSearchable implements ShouldQueue
{
    use Queueable, SerializesModels, WithTags;

    /** @var string the index name which the bulk should be sent */
    public $indexName;

    /** @var \Illuminate\Database\Eloquent\Collection The models to be made searchable. */
    public $models;

    /**
     * Create a new job instance.
     *
     * @param  Collection  $models
     * @param  string|null  $indexName  the index name or alias name
     */
    public function __construct($models, ?string $indexName = null)
    {
        $this->models = $models;

        $this->indexName = $indexName;
    }

    public function handle(): void
    {
        if (count($this->models) === 0) {
            return;
        }

        $firstModel = $this->models->first();

        if (property_exists($this, 'indexName') &&
            $this->indexName &&
            property_exists($firstModel, 'searchableAs')
        ) {
            // this will force the indexer to use another index name
            // probably a full index name instead of an alias
            $firstModel::$searchableAs = $this->indexName;
        }

        $firstModel->searchableUsing()->update($this->models);
    }
}
