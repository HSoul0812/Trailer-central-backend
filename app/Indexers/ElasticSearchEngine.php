<?php

namespace App\Indexers;

use ElasticAdapter\Exceptions\BulkRequestException;
use ElasticAdapter\Indices\Index;
use Illuminate\Support\Facades\Cache;

class ElasticSearchEngine extends \ElasticScoutDriver\Engine
{
    /**
     * Update the given model in the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return void
     * @throws BulkRequestException when some item was not able to be sent
     */
    public function update($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $first = $models->first();

        if ($first &&
            method_exists($first, 'indexConfigurator') &&
            $first->indexConfigurator() &&
            !Cache::get('is_' . $first->indexConfigurator()->name() . '_created', false) &&
            !$this->indexManager->exists($first->indexConfigurator()->name()
            )
        ) {
            // when the index is not create, it will avoid the automatic index creation and
            // will create it with the proper mappings and settings
            $this->indexManager->create(new Index(
                    $first->indexConfigurator()->name(),
                    $first->indexConfigurator()->mapping(),
                    $first->indexConfigurator()->settings()
                )
            );

            Cache::put(
                'is_' . $first->indexConfigurator()->name() . '_created',
                true,
                config('elastic.scout_driver.expiration_index_check_time')
            );
        }

        try {
            parent::update($models);
        } catch (BulkRequestException $exception) {
            // we will need to handle in a better way for each item which was not able to be sent
            throw $exception;
        }
    }
}
