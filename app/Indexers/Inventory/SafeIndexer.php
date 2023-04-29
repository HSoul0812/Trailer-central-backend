<?php

namespace App\Indexers\Inventory;

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
    private const WAIT_TIME_IN_SECONDS = 60;

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
    private $elasticEngine;

    /** @var Collection */
    private $indexes;

    /** @var string|null */
    private $currentIndexName;

    /** @var string */
    private $newIndexName;

    /** @var string */
    private $modelClassName;

    /** @var string date with format Y-m-d H:i:s */
    private $lastUpdateTime;

    public function __construct(Client $client, ConsoleOutput $output, Dispatcher $events)
    {
        $this->client = $client;
        $this->output = $output;
        $this->events = $events;

        $this->model = new Inventory();
        $this->elasticEngine = $this->model->searchableUsing();
        $this->indexAlias = $this->model->indexConfigurator()->aliasName();

        Inventory::$searchableAs = $this->model->indexConfigurator()->name();
        $this->newIndexName = Inventory::$searchableAs;

        $this->indexes = $this->getIndexes();
        // the current index will help to rollback if something goes wrong at swapping time
        $this->currentIndexName = $this->getCurrentIndex($this->indexes);

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
    private function getCurrentIndex(Collection $indexes): ?string
    {
        return $indexes->filter(function (array $aliases): bool {
            return array_key_exists($this->indexAlias, $aliases['aliases']);
        })->map(function (array $aliases, string $indexName): string {
            return $indexName;
        })->first(function (string $indexName): string {
            return $indexName;
        });
    }

    /**
     * @throws IndexSwappingException when a critical issue has occurs in swapping time
     * @throws Exception when some unknown error has been thrown
     */
    public function ingest(): void
    {
        // because a single inventory payload might be up to 230kb and we could have 1000 units per bulk
        ini_set('memory_limit', '256MB');

        $this->numberUnitsToBeProcessed = $this->model->newQuery()->count('inventory_id');

        // ensure it will not have name collisions
        // it happens when we have a physical index using the name as the upcoming index alias,
        // thus it needs to apply a cloning strategy to rename such index
        $this->elasticEngine->ensureIndexDoesNotExists($this->indexAlias);

        $this->lastUpdateTime = $this->getLastUpdateTime();

        $this->createNewIndexToIngestEverything();

        $indexHasBeenSwapped = $this->tryToDoEarlySwapping();

        $this->dispatchAndMonitorMainInventoryIngestion();
        $this->dispatchAndMonitorRecentlyUpdatedInventoryIngestion();

        // it will delete all indexes excepts the current one, so it may help as rollback
        $indexesToDelete = $this->indexes->filter(function (array $aliases, string $indexName): bool {
            return $indexName !== $this->currentIndexName;
        })->toArray();

        $numberOfDocuments = $this->elasticEngine->numberOfDocuments($this->newIndexName);

        // It only has to proceed to index swapping when the number of units already indexed are greater or equals
        // to 97% or units which should be indexed

        // no risky order to finish the process is:
        //  1) try to swap indexes, if everything goes fine
        //  2) proceed to delete al indexes
        if (!$indexHasBeenSwapped && $numberOfDocuments >= $this->numberUnitsToBeProcessed * 0.97) {
            $this->swapIndexes();
        }

        $this->elasticEngine->deleteIndexes($indexesToDelete);
    }

    private function createNewIndexToIngestEverything(): void
    {
        $this->elasticEngine->createIndex(
            $this->newIndexName,
            [
                'mapping' => $this->model->indexConfigurator()->mapping(),
                'settings' => $this->model->indexConfigurator()->settings()
            ]
        );
    }

    /**
     * Basically, this should be done in development environments where the index inventory doesn't exists yet
     */
    private function tryToDoEarlySwapping(): bool
    {
        if (!$this->elasticEngine->indexExists($this->indexAlias)) {
            // early aliasing

            // this edge case happens only in development environments when we have an index named `inventory`
            // before migration from ES6 the ES7 cluster had a real index with that name.
            // this will alias the new index and any queued job will use it
            $this->elasticEngine->swapIndexNames($this->newIndexName, $this->indexAlias);

            return true;
        }

        return false;
    }

    private function getLastUpdateTime(): string
    {
        // @todo we need to consider another potential source of changes like dealer, payment calculator,
        //       dealer location and overlay settings updating.
        //       for now, this command should be ran in time frames where those changes would not happen

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
    private function dispatchAndMonitorRecentlyUpdatedInventoryIngestion(): void
    {
        // given it could be some record which was changed/added between main ingesting process and
        // the index swapping process, so, we need to cover them by pulling them once again and ingest them
        $this->output->writeln(
            'Checking if some records were affected while the main ingestion was working...'
        );

        $numberUnitsToBeProcessed = $this->model->newQuery()
            ->where('updated_at_auto', '>', $this->lastUpdateTime)
            ->count('inventory.inventory_id');

        if ($numberUnitsToBeProcessed) {
            Job::batch(
                function (BatchedJob $batch) use ($numberUnitsToBeProcessed) {
                    $this->output->writeln(
                        sprintf(
                            'It will ingest <comment>%d</comment> records to the index [%s]...',
                            $numberUnitsToBeProcessed,
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
                        ->each(function (Dealer $dealer): void {
                            $this->model->newQuery()
                                ->select('inventory.inventory_id')
                                ->with('user', 'user.website', 'dealerLocation')
                                ->where('inventory.dealer_id', $dealer->dealer_id)
                                ->where('updated_at_auto', '>', $this->lastUpdateTime)
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
     * it will alias the new index to `inventory`, then it will remove alias from previous index,
     * if it fails removing alias from previous one, then it will try to remove alias from new index.
     *
     * @throws IndexSwappingException when a critical issue has occurs in swapping time
     */
    private function swapIndexes(): void
    {
        $this->elasticEngine->putAlias($this->newIndexName, $this->indexAlias);

        if ($this->currentIndexName) {
            try {
                $this->elasticEngine->deleteAlias($this->currentIndexName, $this->indexAlias);
            } catch (Exception $exception) {
                Log::critical(
                    $exception->getMessage(),
                    ['indexName' => $this->currentIndexName, 'alias' => $this->indexAlias]
                );

                try {
                    // to avoid duplicated aliasing
                    $this->elasticEngine->deleteAlias($this->newIndexName, $this->indexAlias);
                } catch (Exception $rollbackException) {
                    Log::emergency(
                        sprintf('[swapIndexes.rollback] %s', $rollbackException->getMessage()),
                        ['indexName' => $this->newIndexName, 'alias' => $this->indexAlias]
                    );

                    throw new IndexSwappingException($exception->getMessage(), $exception->getCode(), $exception);
                }
            }
        }
    }
}
