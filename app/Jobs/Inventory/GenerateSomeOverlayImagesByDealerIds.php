<?php

namespace App\Jobs\Inventory;

use App\Contracts\LoggerServiceInterface;
use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Support\LazyCollection;

/**
 * this job is handling 3 processes
 *       a) Generate overlay
 *            a.1) It only will consider inventories which should have overlays but they dont have
 *            a.2) It will wait for this process when $waitForOverlays is true
 *       b) ElasticSearch indexation
 *       c) Inventory cache invalidation
 */
class GenerateSomeOverlayImagesByDealerIds extends Job
{
    /** @var int time in seconds */
    private const WAIT_TIME_FOR_INDEXATION_IN_SECONDS = 15;

    /** @var int time in seconds */
    private const WAIT_TIME_FOR_GENERATION_IN_SECONDS = 20;

    /** @var string */
    private const MONITORED_GROUP = 'inventory-generate-some-overlay-images-by-dealers';

    /**  @var array<integer> */
    private $dealerIds;

    /** @var string */
    public $queue = 'batched-jobs';

    /**  @var array|null */
    private $context;

    /** @var int The number of times the job may be attempted. */
    public $tries = 1;

    /** @var array|bool */
    private $waitForOverlays;

    public function __construct(array $dealerIds, bool $waitForOverlays, array $context = [])
    {
        $this->dealerIds = $dealerIds;
        $this->context = [];
        $this->waitForOverlays = $waitForOverlays;
    }

    public function handle(
        InventoryResponseCacheInterface $responseCache,
        ResponseCacheKeyInterface $responseCacheKey,
        InventoryRepositoryInterface $repository,
        InventoryServiceInterface $service,
        LoggerServiceInterface $logger
    ): void {
        foreach ($this->dealerIds as $dealerId) {
            $this->context['dealer_id'] = $dealerId;

            /** @var LazyCollection|Inventory[] $inventories */
            $inventories = $repository->getInventoryByDealerIdWhichShouldHaveImageOverlayButTheyDoesNot($dealerId);

            if ($inventories->count() > 0) {
                $logger->info(
                    'Enqueueing the job to generate inventory image overlays for dealer id',
                    ['dealer_id' => $dealerId]
                );

                if ($this->waitForOverlays) {
                    Job::batch(
                        function (BatchedJob $job) use ($inventories) {
                            $this->dispatchImageOverlayJobs($inventories);
                        },
                        [GenerateOverlayImageJob::LOW_PRIORITY_QUEUE],
                        self::MONITORED_GROUP.'-generation-'.$dealerId,
                        self::WAIT_TIME_FOR_GENERATION_IN_SECONDS,
                        array_merge($this->context, ['process' => 'image-overlay-generation'])
                    );
                } else {
                    $this->dispatchImageOverlayJobs($inventories);
                }
            }

            $logger->info('Enqueueing the job to reindex inventory by dealer id', ['dealer_id' => $dealerId]);

            Job::batch(
                static function (BatchedJob $batch) use ($dealerId): void {
                    Inventory::makeAllSearchableByDealers([$dealerId]);
                },
                ['scout'],
                self::MONITORED_GROUP.'-indexation-'.$dealerId,
                self::WAIT_TIME_FOR_INDEXATION_IN_SECONDS,
                array_merge($this->context, ['process' => 'indexation'])
            );

            $logger->info('Enqueueing the job to invalidate cache by dealer id', ['dealer_id' => $dealerId]);

            $responseCache->forget([
                $responseCacheKey->deleteByDealer($dealerId),
                $responseCacheKey->deleteSingleByDealer($dealerId)
            ]);
        }
    }

    /**
     * @param  LazyCollection|Inventory[]  $inventories
     * @return void
     */
    private function dispatchImageOverlayJobs(LazyCollection $inventories)
    {
        foreach ($inventories as $inventory) {
            dispatch(new GenerateOverlayImageJob($inventory->inventory_id, false))
                ->onQueue(GenerateOverlayImageJob::LOW_PRIORITY_QUEUE);
        }
    }
}
