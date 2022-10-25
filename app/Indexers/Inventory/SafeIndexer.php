<?php

namespace App\Indexers\Inventory;

use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Builder;
use App\Indexers\ElasticSearchEngine;
use App\Models\Inventory\Inventory;
use App\Constants\Date;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;

class SafeIndexer
{
    public const RECORDS_PER_BULK = 1000;

    /** @var ElasticSearchEngine * */
    private $indexManager;

    /** @var ConsoleOutput * */
    private $output;

    /** @var int */
    private $numberOfUnitsProcessed;

    /** @var int */
    private $numberUnitsToBeProcessed;

    /** @var string */
    private $indexName;

    /** @var string */
    private $indexAlias;

    /** @var Client */
    private $client;

    public function __construct(Client $client, ConsoleOutput $output)
    {
        $this->client = $client;
        $this->output = $output;
    }

    /**
     * @throws Exception when some unknown error has been thrown
     */
    public function ingest(): void
    {
        // because a single inventory payload might be up to 230kb
        ini_set('memory_limit', '256MB');

        $model = new Inventory();

        $this->indexAlias = $model->indexConfigurator()->aliasName();
        $this->indexName = $model::$searchableAs = $model->indexConfigurator()->name();

        $indexList = $this->getIndexList();

        $this->indexManager = $model->searchableUsing();

        $this->numberUnitsToBeProcessed = $model->newQuery()->count('inventory_id');

        $this->numberOfUnitsProcessed = self::RECORDS_PER_BULK;

        // we must to avoid index name collisions, so, when we have a physical index using the name as the upcoming index alias
        // then we need to apply a cloning strategy to rename such index
        $this->indexManager->ensureIndexDoesNotExists($this->indexAlias);

        $now = now(); // bear in mind the timezone

        $itIsAlreadySwapped = false;

        $this->indexManager->createIndex(
            $this->indexName,
            ['mapping' => $model->indexConfigurator()->mapping(), 'settings' => $model->indexConfigurator()->settings()]
        );

        if (!$this->indexManager->isIndexAlreadyCreated($this->indexAlias)) {
            $this->indexManager->swapIndexNames($this->indexName, $this->indexAlias);
            $itIsAlreadySwapped = true;
        }

        $query = $model->newQuery();

        $this->chunkHandler($query);

        if (!$itIsAlreadySwapped) {
            $this->indexManager->swapIndexNames($this->indexName, $this->indexAlias);
        }

        $this->indexManager->purgeIndexList($indexList->toArray());

        // given it could be some record which was changed/added between main indexing process and index swapping process
        // then we need to cover them
        $query = $model->newQuery()->where('updated_at_auto', '>=', $now->format(Date::FORMAT_Y_M_D_T));
        $this->numberUnitsToBeProcessed += $query->count('inventory_id');

        $this->chunkHandler($query);
    }

    /**
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    public function getIndexList()
    {
        return collect($this->client->indices()->getAlias())->filter(function ($info, $indexName) {
            return strstr($indexName, $this->indexAlias);
        });
    }

    protected function chunkHandler(Builder $query): void
    {
        $query->chunk(self::RECORDS_PER_BULK, function ($models): void {
            try {
                $this->indexManager->update($models);

                $this->output->write(
                    sprintf('[%s] %d of %d',
                        $this->indexName,
                        $this->numberOfUnitsProcessed,
                        $this->numberUnitsToBeProcessed)
                );

                $this->numberOfUnitsProcessed += self::RECORDS_PER_BULK;
            } catch (Exception $e) {
                // to avoid any interruption
            }
        });
    }
}
