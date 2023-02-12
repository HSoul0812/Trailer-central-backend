<?php

namespace App\Indexers\Inventory;

use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
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

    public const INGEST_BY_DEALER = 'by_dealer';
    public const INGEST_REGULAR = 'regular';

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

    /** @var UserRepositoryInterface */
    private $dealerRepository;

    public function __construct(UserRepositoryInterface $dealerRepository, Client $client, ConsoleOutput $output)
    {
        $this->dealerRepository = $dealerRepository;
        $this->client = $client;
        $this->output = $output;
    }

    /**
     * @throws Exception when some unknown error has been thrown
     */
    public function ingest(): void
    {
        // because a single inventory payload might be up to 230kb and we could have 1000 units per bulk
        ini_set('memory_limit', '256MB');

        $model = new Inventory();
        $this->indexAlias = $model->indexConfigurator()->aliasName();
        $this->indexName = $model::$searchableAs = $model->indexConfigurator()->name();

        $indexList = $this->getIndexList();

        $this->indexManager = $model->searchableUsing();

        $this->numberUnitsToBeProcessed = $model->newQuery()->count('inventory_id');

        $this->numberOfUnitsProcessed = 0;

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
            // this edge case happens only in the development environment when we've removed by manually any index
            // so, this will alias the current index and any queued job will use it
            $this->indexManager->swapIndexNames($this->indexName, $this->indexAlias);
            $itIsAlreadySwapped = true;
        }

        /** @var User[] $dealerList */
        $dealerList = $this->dealerRepository->getAll([]);

        foreach ($dealerList as $dealer) {
            $this->chunkHandler(
                  $model->newQuery()
                        ->with('user', 'user.website', 'dealerLocation')
                        ->where('dealer_id', $dealer->dealer_id)
            );
        }

        if (!$itIsAlreadySwapped) {
            $this->indexManager->swapIndexNames($this->indexName, $this->indexAlias);
        }

        $this->indexManager->purgeIndexList($indexList->toArray());

        // given it could be some record which was changed/added between main ingesting process and the index swapping process
        // so, we need to cover them by pulling them once again and ingest them
        $query = $model->newQuery()
                ->with('user', 'user.website', 'dealerLocation')
                ->where('updated_at', '>=', $now->format(Date::FORMAT_Y_M_D_T));

        $this->numberUnitsToBeProcessed = $query->count('inventory_id');
        $this->numberOfUnitsProcessed = 0;

        if ($this->numberUnitsToBeProcessed > 0) {
            $this->output->writeln('<comment>Checking some records affected while the main ingestion was working...</comment>');
        }

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
                $this->numberOfUnitsProcessed += $models->count();

                $this->output->writeln(
                    sprintf('<comment>[%s]</comment> processing %d of %d',
                        $this->indexName,
                        $this->numberOfUnitsProcessed,
                        $this->numberUnitsToBeProcessed)
                );
            } catch (Exception $e) {
                $this->output->writeln(sprintf('<error>[%s] at %s of %d</error>', $e->getMessage(), $e->getFile(), $e->getLine()));
                // to avoid any interruption
            }
        });
    }
}
