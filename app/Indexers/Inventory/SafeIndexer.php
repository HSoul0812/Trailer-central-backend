<?php

namespace App\Indexers\Inventory;

use App\Exceptions\ElasticSearch\IndexPurgingException;
use App\Exceptions\ElasticSearch\IndexSwappingException;
use App\Indexers\ElasticSearchEngine;
use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Models\User\User as Dealer;
use Elasticsearch\Client;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Events\ModelsImported;
use Symfony\Component\Console\Output\ConsoleOutput;

class SafeIndexer
{
    /** @var string[] list of queues which are monitored */
    private const MONITORED_QUEUES = ['scout'];

    /** @var string */
    private const MONITORED_GROUP = 'inventory-recreate-index';

    /** @var int time in seconds */
    private const WAIT_TIME_IN_SECONDS = 90;

    /** @var ConsoleOutput */
    private $output;

    /** @var int */
    private $numberUnitsToBeProcessed;

    /** @var string */
    private $indexAlias;

    /** @var Client */
    private $client;

    /** @var Dispatcher */
    private $events;

    /** @var Inventory */
    private $model;

    /** @var ElasticSearchEngine */
    private $indexManager;

    /** @var Collection */
    private $indexesToPurge;

    /** @var string|null */
    private $currentIndexName;

    /** @var string */
    private $newIndexName;

    /** @var string */
    private $modelClassName;

    public function __construct(Client $client, ConsoleOutput $output, Dispatcher $events)
    {
        $this->client = $client;
        $this->output = $output;
        $this->events = $events;

        $this->model = new Inventory();
        $this->indexManager = $this->model->searchableUsing();
        $this->indexAlias = $this->model->indexConfigurator()->aliasName();

        Inventory::$searchableAs = $this->model->indexConfigurator()->name();
        $this->newIndexName = Inventory::$searchableAs;

        $this->indexesToPurge = $this->getIndexes();
        // the current index will help to rollback if something goes wrong at swapping time
        $this->currentIndexName = $this->getCurrentIndex($this->indexesToPurge);

        $this->modelClassName = get_class($this->model);
    }

    /**
     * Gets indexes which match with the pattern like `inventory_`
     */
    private function getIndexes(): Collection
    {
        return collect($this->client->indices()->getAlias())->filter(function ($info, $indexName) {
            return strstr($indexName, $this->indexAlias);
        });
    }

    /**
     * Gets the current index which has the specific alias
     */
    private function getCurrentIndex(Collection $indexesToPurge): ?string
    {
        return $indexesToPurge->filter(function (array $indexWithAliases): bool {
            return array_key_exists($this->indexAlias, $indexWithAliases['aliases']);
        })->map(function (array $aliases, string $indexName): string {
            return $indexName;
        })->first(function (string $indexName): string {
            return $indexName;
        });
    }

    /**
     * @throws IndexPurgingException when it was impossible to remove the alias from an index
     * @throws IndexSwappingException when a critical issue has occurs in swapping time
     * @throws IndexPurgingException when some elastic client request has failed
     * @throws Exception when some unknown error has been thrown
     */
    public function ingest(): void
    {
        // because a single inventory payload might be up to 230kb and we could have 1000 units per bulk
        ini_set('memory_limit', '256MB');

        $this->numberUnitsToBeProcessed = $this->model->newQuery()->count('inventory_id');

        $this->ensureItWIllNotHaveIndexCollisionName();

        $lastUpdateTime = $this->getLastUpdateTime();

        $this->createNewIndexToIngestEverything();

        $itHasBeenAlreadySwapped = $this->tryToDoEarlySwapping();

        $this->dispatchAndMonitorMainInventoryIngestion();
        $this->dispatchAndMonitorRecentlyUpdatedInventory($lastUpdateTime);

        $this->purgeIndexes();
        $this->swapIndexes($itHasBeenAlreadySwapped);
    }

    /**
     * It must avoid index name collisions, it happens when we have a physical index using the name
     * as the upcoming index alias, therefore it needs to apply a cloning strategy to rename such index
     */
    private function ensureItWIllNotHaveIndexCollisionName(): void
    {
        $this->indexManager->ensureIndexDoesNotExists($this->indexAlias);
    }

    private function createNewIndexToIngestEverything(): void
    {
        $this->indexManager->createIndex(
            $this->newIndexName,
            [
                'mapping' => $this->model->indexConfigurator()->mapping(),
                'settings' => $this->model->indexConfigurator()->settings()
            ]
        );
    }

    /**
     * Basically this should be done in development environments where the index inventory doesn't exists yet
     */
    private function tryToDoEarlySwapping(): bool
    {
        if (!$this->indexManager->indexExists($this->indexAlias)) {
            // early aliasing

            // this edge case happens only in the development environment when we've removed by manually any index
            // so, this will alias the new index and any queued job will use it
            $this->indexManager->swapIndexNames($this->newIndexName, $this->indexAlias);

            return true;
        }

        return false;
    }

    private function getLastUpdateTime(): string
    {
        // @todo we need to consider another potential source of changes like dealer, payment calculator,
        //       dealer location and overlay settings updating
        // for now, this command should be ran in time frames where those changes wont happen

        return $this->model->newQuery()->max('updated_at_auto');
    }

    /**
     * Dispatches the jobs to ingest all the inventory, then it waits until all jobs are processed
     */
    private function dispatchAndMonitorMainInventoryIngestion(): void
    {
        Job::batch(
            function (BatchedJob $batch) {
                $this->output->writeln(
                    sprintf(
                        'It will ingest <comment>%d</comment> records to the index [%s]...',
                        $this->numberUnitsToBeProcessed,
                        $this->newIndexName
                    )
                );
                $this->output->writeln(sprintf('Working on batch <comment>%s</comment> ...', $batch->batch_id));

                $this->events->listen(ModelsImported::class, function ($event) {
                    $lastInventoryId = $event->models->last()->inventory_id;

                    $this->output->writeln(
                        sprintf(
                            '<comment>Dispatched jobs to reindex [%s] models up to ID:</comment> %d',
                            $this->modelClassName,
                            $lastInventoryId
                        )
                    );
                });

                Inventory::makeAllSearchable();

                $this->events->forget(ModelsImported::class);

                $this->output->writeln(sprintf('Waiting for batch <comment>%s</comment> ...', $batch->batch_id));
            },
            self::MONITORED_QUEUES,
            self::MONITORED_GROUP.'-'.'main',
            self::WAIT_TIME_IN_SECONDS
        );
    }

    /**
     * Dispatches the jobs to ingest the inventory which were updated since the main ingestion started,
     * then it waits until all jobs are processed
     */
    private function dispatchAndMonitorRecentlyUpdatedInventory(string $lastUpdateTime): void
    {
        // given it could be some record which was changed/added between main ingesting process and
        // the index swapping process, so, we need to cover them by pulling them once again and ingest them
        $this->output->writeln(
            'Checking if some records were affected while the main ingestion was working...'
        );

        $this->numberUnitsToBeProcessed = $this->model->newQuery()
            ->where('updated_at_auto', '>', $lastUpdateTime)
            ->count('inventory.inventory_id');

        if ($this->numberUnitsToBeProcessed) {
            Job::batch(
                function (BatchedJob $batch) use ($lastUpdateTime) {
                    $this->output->writeln(
                        sprintf(
                            'It will ingest <comment>%d</comment> records to the index [%s]...',
                            $this->numberUnitsToBeProcessed,
                            $this->newIndexName
                        )
                    );

                    $this->output->writeln(sprintf('Working on batch <comment>%s</comment> ...', $batch->batch_id));

                    $this->events->listen(ModelsImported::class, function ($event) {
                        $lastInventoryId = $event->models->last()->inventory_id;

                        $this->output->writeln(
                            sprintf(
                                '<comment>Dispatched jobs to reindex [%s] models up to ID:</comment> %d',
                                $this->modelClassName,
                                $lastInventoryId
                            )
                        );
                    });

                    Dealer::query()->select('dealer.dealer_id')
                        ->get()
                        ->each(function (Dealer $dealer) use ($lastUpdateTime): void {
                            $this->model->newQuery()
                                ->select('inventory.inventory_id')
                                ->with('user', 'user.website', 'dealerLocation')
                                ->where('inventory.dealer_id', $dealer->dealer_id)
                                ->where('updated_at_auto', '>', $lastUpdateTime)
                                ->searchable();
                        });

                    $this->events->forget(ModelsImported::class);

                    $this->output->writeln(sprintf('Waiting for batch <comment>%s</comment> ...', $batch->batch_id));
                },
                self::MONITORED_QUEUES,
                self::MONITORED_GROUP.'-'.'remaining',
                self::WAIT_TIME_IN_SECONDS
            );
        }
    }

    /**
     * @throws IndexPurgingException when it was impossible to remove the alias from an index
     * @throws IndexPurgingException when some elastic client request has failed
     */
    private function purgeIndexes(): void
    {
        try {
            $this->indexManager->purgeIndexes($this->indexesToPurge->toArray(), $this->indexAlias);
        } catch (Exception $exception) {
            // deleting alias should interrups immediately any subsequent process
            Log::critical(
                $exception->getMessage(),
                ['indexName' => $this->newIndexName, 'alias' => $this->indexAlias]
            );

            throw new IndexPurgingException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws IndexSwappingException when a critical issue has occurs in swapping time
     */
    private function swapIndexes(bool $itHasBeenAlreadySwapped): void
    {
        if (!$itHasBeenAlreadySwapped) {
            try {
                $this->indexManager->swapIndexNames($this->newIndexName, $this->indexAlias);
            } catch (Exception $exception) {
                Log::emergency(
                    $exception->getMessage(),
                    ['indexName' => $this->newIndexName, 'alias' => $this->indexAlias]
                );

                if ($this->currentIndexName) {
                    // rollback to the previous index
                    try {
                        $this->indexManager->swapIndexNames($this->currentIndexName, $this->indexAlias);

                    } catch (Exception $rollbackException) {
                        Log::emergency(
                            $rollbackException->getMessage(),
                            ['indexName' => $this->currentIndexName, 'alias' => $this->indexAlias]
                        );
                    }
                }

                throw new IndexSwappingException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
    }
}
