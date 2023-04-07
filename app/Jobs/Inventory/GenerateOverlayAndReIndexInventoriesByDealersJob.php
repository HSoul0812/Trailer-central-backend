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

/**
 * @todo this job should be renamed to `InventoryBackGroundWorkFlowByDealerJob` when safe, it is is handling 3 processes
 *       a) Generate overlay
 *       b) ElasticSearch indexation
 *       c) Inventory cache invalidation
 */
class GenerateOverlayAndReIndexInventoriesByDealersJob extends Job
{
    /** @var int time in seconds */
    private const WAIT_TIME = 15;

    /**  @var array<integer> */
    private $dealerIds;

    /** @var string */
    public $queue = 'overlay-images';

    /**  @var array|null */
    private $context;

    /** @var int The number of times the job may be attempted. */
    public $tries = 1;

    public function __construct(array $dealerIds, ?array $context = null)
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

            $inventories = $repository->getAll(
                ['dealer_id' => $dealerId, 'images_greater_than' => 1],
                false,
                false,
                [Inventory::getTableName().'.inventory_id']
            );

            if ($inventories->count() > 0) {
                $logger->info(
                    'Enqueueing the job to generate inventory image overlays for dealer id',
                    ['dealer_id' => $dealerId]
                );

                Job::batch(static function (BatchedJob $job) use ($inventories) {
                    foreach ($inventories as $inventory) {
                        dispatch(new GenerateOverlayImageJob($inventory->inventory_id, false));
                    }
                }, __CLASS__, 2, array_merge($this->context, ['process' => 'image-overlay-generation']));
            }

            $logger->info('Enqueueing the job to reindex inventory by dealer id', ['dealer_id' => $dealerId]);

            Job::batch(function (BatchedJob $batch) use ($dealerId): void {
                Inventory::makeAllSearchableByDealers([$dealerId]);
            }, __CLASS__, self::WAIT_TIME, array_merge($this->context, ['process' => 'indexation']));

            $logger->info('Enqueueing the job to invalidate cache by dealer id', ['dealer_id' => $dealerId]);

            $responseCache->forget([
                $responseCacheKey->deleteByDealer($dealerId),
                $responseCacheKey->deleteSingleByDealer($dealerId)
            ]);
        }
    }
}
