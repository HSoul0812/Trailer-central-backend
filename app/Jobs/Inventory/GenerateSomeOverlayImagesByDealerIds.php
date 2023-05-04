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
use Illuminate\Database\Eloquent\Collection;

/**
 * @todo this job is a work in progress, it is handling 3 processes
 *       a) Generate overlay (It only should consider inventories which were updated from a specific date)
 *       b) ElasticSearch indexation
 *       c) Inventory cache invalidation
 */
class GenerateSomeOverlayImagesByDealerIds extends Job
{
    /** @var int time in seconds */
    private const WAIT_TIME_FOR_INDEXATION_IN_SECONDS = 15;

    /** @var int time in seconds */
    private const WAIT_TIME_FOR_GENERATION_IN_SECONDS = 20;

    /** @var string  */
    private const MONITORED_GROUP = 'inventory-generate-some-overlay-images-by-dealers';

    /**  @var array<integer> */
    private $dealerIds;

    /** @var string */
    public $queue = 'batched-jobs';

    /**  @var array|null */
    private $context;

    /** @var int The number of times the job may be attempted. */
    public $tries = 1;

    public function __construct(array $dealerIds, array $context = [])
    {
        $this->dealerIds = $dealerIds;
        $this->context = $context ?? [];
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

            /** @var Collection|Inventory[] $inventories */
            // @todo we need to get only those inventories which have images and they need need to be overlay.
            //       since this is not being used, it is not problematic, only a work in progress to take advantage
            //       of previous implementation
            $inventories = new Collection();

            if ($inventories->count() > 0) {
                $logger->info(
                    'Enqueueing the job to generate inventory image overlays for dealer id',
                    ['dealer_id' => $dealerId]
                );

                Job::batch(
                    static function (BatchedJob $job) use ($inventories) {
                        foreach ($inventories as $inventory) {
                            dispatch(new GenerateOverlayImageJob($inventory->inventory_id, false));
                        }
                    },
                    [GenerateOverlayImageJob::LOW_PRIORITY_QUEUE],
                    self::MONITORED_GROUP.'-p1-'.$dealerId,
                    self::WAIT_TIME_FOR_GENERATION_IN_SECONDS,
                    array_merge($this->context, ['process' => 'image-overlay-generation'])
                );
            }

            $logger->info('Enqueueing the job to reindex inventory by dealer id', ['dealer_id' => $dealerId]);

            Job::batch(
                function (BatchedJob $batch) use ($dealerId): void {
                    Inventory::makeAllSearchableByDealers([$dealerId]);
                },
                ['scout'],
                self::MONITORED_GROUP.'-p2-'.$dealerId,
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
}
