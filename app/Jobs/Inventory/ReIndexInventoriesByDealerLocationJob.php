<?php

namespace App\Jobs\Inventory;

use App\Contracts\LoggerServiceInterface;
use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Models\Inventory\Inventory;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;

/**
 * Will handle the Scout re-indexation by dealer location
 */
class ReIndexInventoriesByDealerLocationJob extends Job
{
    /** @var int time in seconds */
    private const WAIT_TIME_IN_SECONDS = 15;

    /** @var string[] list of queues which are monitored */
    private const MONITORED_QUEUES = ['scout'];

    /** @var string  */
    private const MONITORED_GROUP = 'inventory-reindex-by-dealer-location';

    /** @var array<integer> */
    private $locationId;

    /**  @var array|null */
    private $context;

    public $queue = 'batched-jobs';

    /** @var int The number of times the job may be attempted. */
    public $tries = 1;

    public function __construct(int $locationId, ?array $context = null)
    {
        $this->locationId = $locationId;
        $this->context = $context ?? ['location_id' => $locationId];
    }

    public function handle(
        DealerLocationRepositoryInterface $repository,
        InventoryResponseCacheInterface $responseCache,
        ResponseCacheKeyInterface $responseCacheKey,
        LoggerServiceInterface $logger
    ): void {
        $dealerLocation = $repository->get(['dealer_location_id' => $this->locationId]);

        $logContext = [
            'name' => $dealerLocation->name,
            'dealer_id' => $dealerLocation->dealer_id,
            'dealer_location_id' => $dealerLocation->dealer_location_id
        ];

        $logger->info(
            'Enqueueing the job to reindex inventory by dealer location',
            $logContext
        );

        Job::batch(
            function (BatchedJob $batch): void {
                Inventory::makeAllSearchableByDealerLocationId($this->locationId);
            },
            self::MONITORED_QUEUES,
            self::MONITORED_GROUP.'-'.$dealerLocation->dealer_location_id,
            self::WAIT_TIME_IN_SECONDS,
            array_merge($this->context, ['dealer_id' => $dealerLocation->dealer_id,])
        );

        $logger->info(
            'Enqueueing the job to invalidate cache by dealer location',
            $logContext
        );

        $responseCache->forget([
            $responseCacheKey->deleteByDealer($dealerLocation->dealer_id),
            $responseCacheKey->deleteSingleByDealer($dealerLocation->dealer_id)
        ]);
    }
}
