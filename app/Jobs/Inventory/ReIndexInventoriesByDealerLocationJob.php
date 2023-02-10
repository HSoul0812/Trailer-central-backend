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
    private const WAIT_TIME = 15;

    /**
     * @var array<integer>
     */
    private $locationId;

    public $queue = 'scout';

    public function __construct(int $locationId)
    {
        $this->locationId = $locationId;
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

        Job::batch(function (BatchedJob $batch): void {
            Inventory::makeAllSearchableByDealerLocationId($this->locationId);
        }, __CLASS__, self::WAIT_TIME);

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
