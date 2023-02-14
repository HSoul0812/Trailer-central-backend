<?php

namespace App\Indexers;

use ElasticAdapter\Exceptions\BulkRequestException;
use ElasticAdapter\Indices\Alias;
use ElasticAdapter\Indices\Index;
use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Scout\Searchable;
use Exception;
use Log;

class ElasticSearchEngine extends \ElasticScoutDriver\Engine
{
    /** @var array<string, bool> */
    private static $indexStatus = [];

    /**
     * Update the given model in the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return void
     * @throws Exception when some unknown error has been thrown
     */
    public function update($models): void
    {
        if (in_array(config('app.env'), ['local', 'dev'])) {
            // in production or staging this is not an issue, but in devs/local environments we need to avoid index auto-creation
            // because the index mapping most of cases are special
            // in production we're using `inventory:recreate-index` command to made sure it is being created with proper mapping
            $this->ensureIndexIsCreated($models);
        }

        try {
            parent::update($models);
        } catch (BulkRequestException $exception) {
            $failedModels = collect($exception->getResponse()['items'])->filter(function (array $item) {
                return isset($item['index']['error']);
            })->map(function ($error) {
                return [
                    'id' => $error['index']['_id'],
                    'reason' => $error['index']['error']['reason']
                ];
            })->toJson();

            // @todo to avoid any potential missing inventory in the ES index due some error at bulk time,
            // we should persist the error somewhere, maybe in MySQL DB, it will be handy to trace any error source
            // at indexation time
            Log::critical($failedModels);
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
        /** @var Model|WithIndexConfigurator|Searchable $first */

        $first = $models->first();

        if ($first &&
            method_exists($first, 'indexConfigurator') &&
            ($configurator = $first->indexConfigurator()) &&
            ($searchableAs = $configurator->aliasName()) &&
            config('elastic.scout_driver.check_index.inventory', true) && // to save a RPC in ES server
            !$this->isIndexAlreadyCreated($searchableAs)
        ) {
            $indexName = $configurator->name();

            $this->createIndex(
                $indexName,
                ['mapping' => $configurator->mapping(), 'settings' => $configurator->settings()]
            );

            self::$indexStatus[$searchableAs] = true;

            if ($configurator->shouldMakeAlias()) {
                $this->indexManager->putAlias($indexName, new Alias($searchableAs));
            }
        }
    }

    /**
     * Checks if a index is already created (or its alias)
     *
     * @param string $indexName
     * @return bool
     */
    public function isIndexAlreadyCreated(string $indexName): bool
    {
        if (!isset(self::$indexStatus[$indexName])) {
            self::$indexStatus[$indexName] = $this->indexManager->exists($indexName);
        }

        return self::$indexStatus[$indexName];
    }

    public function swapIndexNames(string $indexName, string $aliasName): void
    {
        $this->indexManager->putAlias($indexName, new Alias($aliasName));
    }

    public function purgeIndexList(array $list): void
    {
        foreach ($list as $index => $aliasMapping) {
            $this->indexManager->drop($index);
        }
    }

    public function ensureIndexDoesNotExists(string $indexAliasName): void
    {
        $esClient = $this->getElasticClient();

        if ($this->indexManager->exists($indexAliasName) && !$esClient->indices()->existsalias(['name' => $indexAliasName])) {
            $tempIndex = $indexAliasName . '_temp_' . now()->format('YmdHi');

            $esClient->indices()->freeze(['index' => $indexAliasName]);
            $esClient->indices()->clone(['index' => $indexAliasName, 'target' => $tempIndex]);
            $esClient->indices()->delete(['index' => $indexAliasName]);

            $this->indexManager->putAlias($tempIndex, new Alias($indexAliasName));

            $esClient->indices()->unfreeze(['index' => $tempIndex]);
        }
    }

    protected function getElasticClient(): Client
    {
        return app(Client::class);
    }
}
