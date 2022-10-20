<?php

namespace App\Indexers;

use ElasticAdapter\Exceptions\BulkRequestException;
use ElasticAdapter\Indices\Index;
use ElasticMigrations\Facades\Index as EsIndex;
use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

class ElasticSearchEngine extends \ElasticScoutDriver\Engine
{
    /**
     * Update the given model in the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return void
     * @throws BulkRequestException when some item was not able to be sent/updated
     */
    public function update($models): void
    {
        $this->ensureIndexIsCreated($models);

        try {
            parent::update($models);
        } catch (BulkRequestException $exception) {
            // we will need to handle in a better way for each item which was not able to be sent
            throw $exception;
        }
    }

    /**
     * @param string $name
     * @param array{mapping:Mapping|null, settings: Settings|null} $options
     * @return void
     * @throws InvalidArgumentException when the primary key options was provided (it is not possible to change it)
     */
    public function createIndex($name, array $options = []): void
    {
        if (isset($options['primaryKey'])) {
            throw new InvalidArgumentException('It is not possible to change the primary key name');
        }

        $mapping = $options['mapping'] ?? null;
        $settings = $options['settings'] ?? null;

        $this->indexManager->create(new Index($name, $mapping, $settings));
    }

    /**
     * When the model has a index configurator it will ensure the index mapping and settings are properly defined
     */
    private function ensureIndexIsCreated(\Illuminate\Database\Eloquent\Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        /** @var IndexConfigurator $configurator */
        /** @var Model|WithIndexConfigurator $first */

        $first = $models->first();

        if ($first &&
            method_exists($first, 'indexConfigurator') &&
            ($configurator = $first->indexConfigurator()) &&
            !$this->indexManager->exists($first->searchableAs())
        ) {
            $indexName = $configurator->name() . '_' . date('YmdHi');

            $this->createIndex(
                $indexName,
                ['mapping' => $configurator->mapping(), 'settings' => $configurator->settings()]
            );
        }
    }

    public function safeSyncImporter(Model $model, string $indexName): void
    {
        if (!method_exists($model, 'usesSoftDelete')) {
            throw new \InvalidArgumentException('The model must be searchable type');
        }

        $model::$searchableAs = $indexName;

        $this->ensureIndexDoesNotExists($model::ALIAS_ES_NAME);

        $softDelete = $model::usesSoftDelete() && config('scout.soft_delete', false);

        $now = Date::now(); // bear in mind the timezome

        $this->createIndex(
            $indexName,
            ['mapping' => $model->indexConfigurator()->mapping(), 'settings' => $model->indexConfigurator()->settings()]
        );

        $query = $model->newQuery()
            ->when($softDelete, function ($query) {
                $query->withTrashed();
            })
            ->orderBy($model->getKeyName());

        $this->chunkQueryImport($query);

        $this->swapIndexNames($model);

        $query = $model->newQuery()->where('updated_at_auto', '>=' , $now->format(\App\Constants\Date::FORMAT_Y_M_D_T))
            ->when($softDelete, function ($query) {
                $query->withTrashed();
            })
            ->orderBy($model->getKeyName());

        $this->chunkQueryImport($query);

    }

    /**
     * @param $query
     * @return void
     */
    protected function chunkQueryImport($query): void
    {
        $query->chunk(100, function ($models) use ($query) {
            try {
                $query->first()->searchableUsing()->update($models);
            } catch (BulkRequestException $e) {

            }
        });
    }

    public function swapIndexNames(Model $model): void
    {
        $esClient = app(Client::class);

        $aliases = $esClient->indices()->getAliases();

        $this->indexManager->putAlias($model::$searchableAs, new \ElasticAdapter\Indices\Alias($model::ALIAS_ES_NAME));

        foreach ($aliases as $index => $aliasMapping) {
            if (array_key_exists($model::ALIAS_ES_NAME, $aliasMapping['aliases'])) {
                if ($index == $model::$searchableAs ) {
                    continue;
                } else {
                    $this->indexManager->drop($index);
                }

            }
        }
    }

    public function ensureIndexDoesNotExists(string $indexAliasName): void
    {
        $esClient = app(Client::class);

        if ($this->indexManager->exists($indexAliasName) && !$esClient->indices()->existsalias(['name' => $indexAliasName])) {

            $tempIndex = $indexAliasName . '_temp_' . Date::now()->format('YmdHi');

            $esClient->indices()->freeze(['index' => $indexAliasName]);
            $esClient->indices()->clone(['index' => $indexAliasName, 'target' => $tempIndex]);
            $esClient->indices()->delete(['index' =>$indexAliasName]);

            $this->indexManager->putAlias($tempIndex, new \ElasticAdapter\Indices\Alias($indexAliasName));

            $esClient->indices()->unfreeze(['index' => $tempIndex]);
        }
    }
}
