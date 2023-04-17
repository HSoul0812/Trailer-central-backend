<?php

namespace App\Indexers\Inventory;

use App\Exceptions\ElasticSearch\IndexSwappingException;
use App\Indexers\ElasticSearchEngine;
use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\User\User as Dealer;
use Elasticsearch\Client;
use Illuminate\Contracts\Events\Dispatcher;
use App\Models\Inventory\Inventory;
use Exception;
use Laravel\Scout\Events\ModelsImported;
use Symfony\Component\Console\Output\ConsoleOutput;

class SafeIndexer
{
    /** @var string[] list of queues which are monitored */
    private const MONITORED_QUEUES = ['scout'];

    /** @var int 5 minutes */
    public const ALIASING_RETRY_TIME_IN_SECONDS = 300;

    /** @var int */
    public const ALIASING_RETRY_TIMES = 2;

    /** @var int time in seconds */
    private const WAIT_TIME_IN_SECONDS = 2;

    public const RECORDS_PER_BULK = 500;

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

    public function __construct(Client $client, ConsoleOutput $output, Dispatcher $events)
    {
        $this->client = $client;
        $this->output = $output;
        $this->events = $events;
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
        Inventory::$searchableAs = $model->indexConfigurator()->name();

        $indexesToPurge = $this->getIndexes();

        /** @var ElasticSearchEngine $indexManager */
        $indexManager = $model->searchableUsing();

        $this->numberUnitsToBeProcessed = $model->newQuery()->count('inventory_id');

        // we must to avoid index name collisions, so, when we have a physical index using the name
        // as the upcoming index alias, then we need to apply a cloning strategy to rename such index
        $indexManager->ensureIndexDoesNotExists($this->indexAlias);

        // @todo we need to consider another potential source of changes like dealer, payment calculator,
        //       dealer location and overlay settings updating
        // for now, this command should be ran in time frames where those changes wont happen
        $lastUpdateTime = $model->newQuery()->max('updated_at_auto');

        $itIsAlreadySwapped = false;

        $indexManager->createIndex(
            Inventory::$searchableAs,
            ['mapping' => $model->indexConfigurator()->mapping(), 'settings' => $model->indexConfigurator()->settings()]
        );

        if (!$indexManager->isIndexAlreadyCreated($this->indexAlias)) {
            // this edge case happens only in the development environment when we've removed by manually any index
            // so, this will alias the current index and any queued job will use it
            $indexManager->swapIndexNames(Inventory::$searchableAs, $this->indexAlias);
            $itIsAlreadySwapped = true;
        }

        Job::batch(
            function (BatchedJob $batch) use ($model) {
                $this->output->writeln(
                    sprintf(
                        'It will ingest <comment>%d</comment> records to the index [%s]...',
                        $this->numberUnitsToBeProcessed,
                        Inventory::$searchableAs
                    )
                );
                $this->output->writeln(sprintf('Working on batch <comment>%s</comment> ...', $batch->batch_id));

                $this->events->listen(ModelsImported::class, function ($event) use ($model) {
                    $lastInventoryId = $event->models->last()->inventory_id;

                    $this->output->writeln(
                        sprintf(
                            '<comment>Dispatched jobs to reindex [%s] models up to ID:</comment> %d',
                            get_class($model),
                            $lastInventoryId
                        )
                    );
                });

                Inventory::makeAllSearchable();

                $this->events->forget(ModelsImported::class);

                $this->output->writeln(sprintf('Waiting for batch <comment>%s</comment> ...', $batch->batch_id));
            },
            self::MONITORED_QUEUES,
            __CLASS__,
            self::WAIT_TIME_IN_SECONDS
        );

        // given it could be some record which was changed/added between main ingesting process and
        // the index swapping process, so, we need to cover them by pulling them once again and ingest them
        $this->output->writeln(
            'Checking if some records were affected while the main ingestion was working...'
        );

        $this->numberUnitsToBeProcessed = $model->newQuery()
            ->where('updated_at_auto', '>', $lastUpdateTime)
            ->count('inventory.inventory_id');

        if ($this->numberUnitsToBeProcessed) {
            Job::batch(
                function (BatchedJob $batch) use ($model, $lastUpdateTime) {
                    $this->output->writeln(
                        sprintf(
                            'It will ingest <comment>%d</comment> records to the index [%s]...',
                            $this->numberUnitsToBeProcessed,
                            Inventory::$searchableAs
                        )
                    );

                    $this->output->writeln(sprintf('Working on batch <comment>%s</comment> ...', $batch->batch_id));

                    $this->events->listen(ModelsImported::class, function ($event) use ($model) {
                        $lastInventoryId = $event->models->last()->inventory_id;

                        $this->output->writeln(
                            sprintf(
                                '<comment>Dispatched jobs to reindex [%s] models up to ID:</comment> %d',
                                get_class($model),
                                $lastInventoryId
                            )
                        );
                    });

                    Dealer::query()->select('dealer.dealer_id')
                        ->get()
                        ->each(function (Dealer $dealer) use ($model, $lastUpdateTime): void {
                            $model->newQuery()
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
                __CLASS__,
                self::WAIT_TIME_IN_SECONDS
            );
        }

        try {
            retry(
                self::ALIASING_RETRY_TIMES,
                function () use ($indexManager, $indexesToPurge, $itIsAlreadySwapped) {
                    $indexManager->purgeIndexes($indexesToPurge->toArray(), $this->indexAlias);

                    if (!$itIsAlreadySwapped) {
                        $indexManager->swapIndexNames(Inventory::$searchableAs, $this->indexAlias);
                    }
                },
                self::ALIASING_RETRY_TIME_IN_SECONDS
            );
        } catch (Exception $exception) {
            throw new IndexSwappingException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Gets indexes which match with the pattern like `inventory_`
     *
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    public function getIndexes()
    {
        return collect($this->client->indices()->getAlias())->filter(function ($info, $indexName) {
            return strstr($indexName, $this->indexAlias);
        });
    }
}
